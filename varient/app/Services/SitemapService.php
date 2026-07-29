<?php

declare(strict_types=1);

namespace App\Services;

use XMLWriter;
use Generator;

/**
 * Class SitemapService
 *
 * Generates highly scalable and memory-efficient XML sitemaps for the application.
 * Utilizes PHP Generators (yield) and chunked database queries to process
 * hundreds of thousands of records without exhausting server memory.
 * Features automatic file splitting according to Google's URL limits.
 */
class SitemapService
{
    /**
     * Google's maximum URL limit per sitemap file
     * * @var int
     */
    public const MAX_URLS_PER_FILE = 49000;

    /**
     * The number of records to fetch from the database in a single query
     * * @var int
     */
    public const DB_CHUNK_SIZE = 1000;

    /**
     * The active XMLWriter instance used to build the current sitemap file
     * * @var XMLWriter|null
     */
    private ?XMLWriter $xmlWriter = null;

    /**
     * An array storing the URLs of all generated sitemap files
     * * @var array<int, string>
     */
    private array $sitemapIndexFiles = [];

    /**
     * Global application configuration settings
     * * @var object
     */
    private object $config;

    /**
     * Array containing structured language configurations for hreflang tags
     * * @var array<int, array{short_form: string, base_url: string}>
     */
    private array $languages = [];

    /**
     * SitemapService constructor
     *
     * @param object $config The global configuration object.
     */
    public function __construct(object $config)
    {
        $this->config = $config;
        $this->initLanguages();
    }

    /**
     * Main entry point for sitemap generation
     *
     * @return array<int, string> Returns the array of generated sitemap file URLs.
     */
    public function generate(): array
    {
        $this->deleteOldSitemaps();

        // Homepages (Roots with Hreflang)
        $this->processDataset('home', $this->getHomepageSource());

        // Custom Pages (From DB - Independent)
        $this->processDataset('pages', $this->getPagesSource());

        // Posts (Cursor Pagination)
        $this->processDataset('posts', $this->getPostsSource());

        // Categories
        $this->processDataset('categories', $this->getCategoriesSource());

        // Authors (Only those with active posts)
        $this->processDataset('authors', $this->getAuthorsSource());

        // Generate Index File
        $this->generateSitemapIndex();

        return $this->sitemapIndexFiles;
    }

    /**
     * Core Processor: Handles file creation, rotation, and writing logic
     *
     * @param string    $prefix     The prefix for the generated filename (e.g., 'posts', 'categories').
     * @param Generator $dataSource The generator yielding array payloads for each URL node.
     * @return void
     */
    private function processDataset(string $prefix, Generator $dataSource): void
    {
        $fileCounter = 1;
        $urlCounter = 0;

        $this->startNewSitemapFile("sitemap-{$prefix}-{$fileCounter}.xml");

        foreach ($dataSource as $item) {
            // Rotate file if the URL limit is reached
            if ($urlCounter >= self::MAX_URLS_PER_FILE) {
                $this->finishCurrentSitemapFile();
                $fileCounter++;
                $this->startNewSitemapFile("sitemap-{$prefix}-{$fileCounter}.xml");
                $urlCounter = 0;
            }

            $this->writeUrlNode(
                (string)($item['loc'] ?? ''),
                (string)($item['priority'] ?? '0.5'),
                (string)($item['lastmod'] ?? date('c')),
                (string)($item['changefreq'] ?? 'monthly'),
                $item['images'] ?? [],
                $item['alternates'] ?? []
            );

            $urlCounter++;
        }

        $this->finishCurrentSitemapFile();
    }

    /**
     * Generates Homepage Roots with Hreflang and x-default fallback
     *
     * @return Generator Yields array containing root URL configuration.
     */
    private function getHomepageSource(): Generator
    {
        // Build alternate links map
        $alternates = [];
        foreach ($this->languages as $language) {
            $url = $language['base_url'] ?? base_url();
            $shortForm = $language['short_form'] ?? '';
            $alternates[$shortForm] = $url;
        }

        // Add x-default fallback for untargeted regions
        $alternates['x-default'] = base_url();

        // Yield each language root
        foreach ($this->languages as $language) {
            yield [
                'loc'        => $language['base_url'] ?? base_url(),
                'priority'   => '1.0',
                'lastmod'    => date('c'),
                'changefreq' => 'daily',
                'alternates' => $alternates
            ];
        }
    }

    /**
     * Fetches Custom Pages (About, Contact, etc.) from the Database
     *
     * @return Generator Yields array containing page URL configuration.
     */
    private function getPagesSource(): Generator
    {
        $pageModel = model('PageModel');
        $lastId = 0;

        do {
            $pages = $pageModel->select('id, lang_id, slug, status, updated_at, created_at')
                ->where('id >', $lastId)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->findAll(self::DB_CHUNK_SIZE);

            if (empty($pages)) {
                break;
            }

            foreach ($pages as $page) {
                $lastId = (int)$page->id;

                $language = $this->languages[$page->lang_id] ?? null;
                $baseUrl = $language['base_url'] ?? base_url();
                $loc = rtrim($baseUrl, '/') . '/' . $page->slug;

                yield [
                    'loc'        => $loc,
                    'priority'   => '0.5',
                    'lastmod'    => $page->updated_at ?? $page->created_at,
                    'changefreq' => 'monthly'
                ];
            }
            // Free memory explicitly after processing a chunk
            unset($pages);
        } while (true);
    }

    /**
     * Fetches active Posts using Cursor Pagination for high performance
     *
     * @return Generator Yields array containing post URL and image configurations.
     */
    private function getPostsSource(): Generator
    {
        $postModel = model('PostModel');
        $lastId = 0;

        do {
            $posts = $postModel->select('posts.id, posts.title, posts.slug, posts.lang_id, posts.updated_at, posts.created_at, images.image_big, images.storage')
                ->join('images', 'images.id = posts.image_id', 'left')
                ->where('posts.visibility', 1)
                ->where('posts.is_scheduled', 0)
                ->where('posts.status', 1)
                ->where('posts.id >', $lastId)
                ->orderBy('posts.id', 'ASC')
                ->findAll(self::DB_CHUNK_SIZE);

            if (empty($posts)) {
                break;
            }

            foreach ($posts as $post) {
                $lastId = (int)$post->id;

                $language = $this->languages[$post->lang_id] ?? null;
                $baseUrl = $language['base_url'] ?? '';

                $images = [];
                if (!empty($post->image_big)) {
                    $imageUrl = getStorageFileUrl($post->image_big, $post->storage);
                    $images[] = ['loc' => $imageUrl, 'title' => $post->title];
                }

                yield [
                    'loc'        => generatePostUrl($post, $baseUrl),
                    'priority'   => '0.8',
                    'lastmod'    => $post->updated_at ?? $post->created_at,
                    'changefreq' => 'weekly',
                    'images'     => $images
                ];
            }
            // Free memory explicitly
            unset($posts);
        } while (true);
    }

    /**
     * Fetches Categories from the Database
     *
     * @return Generator Yields array containing category URL configurations.
     */
    private function getCategoriesSource(): Generator
    {
        $categoryModel = model('CategoryModel');
        $lastId = 0;

        do {
            $cats = $categoryModel->select('id, slug, updated_at, created_at')
                ->where('id >', $lastId)
                ->orderBy('id', 'ASC')
                ->findAll(self::DB_CHUNK_SIZE);

            if (empty($cats)) {
                break;
            }

            foreach ($cats as $cat) {
                $lastId = (int)$cat->id;

                yield [
                    'loc'        => generateCategoryUrl($cat),
                    'priority'   => '0.6',
                    'lastmod'    => $cat->updated_at ?? date('c'),
                    'changefreq' => 'monthly'
                ];
            }
            unset($cats);
        } while (true);
    }

    /**
     * Fetches Authors who have at least one active, visible post
     *
     * @return Generator Yields array containing author profile URL configurations.
     */
    private function getAuthorsSource(): Generator
    {
        $userModel = model('UserModel');
        $lastId = 0;

        do {
            $authors = $userModel->select('users.id, users.slug, users.username, users.created_at')
                ->distinct()
                ->join('posts', 'posts.user_id = users.id')
                ->where('users.id >', $lastId)
                ->where('users.status', 1)
                ->where('posts.status', 1)
                ->where('posts.visibility', 1)
                ->orderBy('users.id', 'ASC')
                ->findAll(self::DB_CHUNK_SIZE);

            if (empty($authors)) {
                break;
            }

            foreach ($authors as $author) {
                $lastId = (int)$author->id;
                $slug = !empty($author->slug) ? $author->slug : $author->username;

                yield [
                    'loc'        => base_url("profile/{$slug}"),
                    'priority'   => '0.5',
                    'lastmod'    => $author->created_at ?? date('c'),
                    'changefreq' => 'monthly'
                ];
            }
            unset($authors);
        } while (true);
    }

    /**
     * Writes a single URL node into the active XMLWriter instance
     *
     * @param string $loc        The canonical URL.
     * @param string $priority   The priority score (e.g., '0.8').
     * @param string $lastMod    The last modification date.
     * @param string $changeFreq How often the page is expected to change.
     * @param array  $images     Array of image associative arrays.
     * @param array  $alternates Array of hreflang associative arrays.
     * @return void
     */
    private function writeUrlNode(string $loc, string $priority, string $lastMod, string $changeFreq, array $images, array $alternates): void
    {
        $this->xmlWriter->startElement('url');
        $this->xmlWriter->writeElement('loc', $loc);
        $this->xmlWriter->writeElement('lastmod', date('c', strtotime($lastMod)));
        $this->xmlWriter->writeElement('changefreq', $changeFreq);
        $this->xmlWriter->writeElement('priority', $priority);

        // Hreflang Tags (Multilingual SEO)
        foreach ($alternates as $lang => $url) {
            $this->xmlWriter->startElement('xhtml:link');
            $this->xmlWriter->writeAttribute('rel', 'alternate');
            $this->xmlWriter->writeAttribute('hreflang', $lang);
            $this->xmlWriter->writeAttribute('href', $url);
            $this->xmlWriter->endElement();
        }

        // Image Sitemap Tags (Google Image SEO)
        foreach ($images as $img) {
            $this->xmlWriter->startElement('image:image');
            $this->xmlWriter->writeElement('image:loc', $img['loc']);

            if (!empty($img['title'])) {
                // Ensure the title is safely encoded for XML
                $cleanTitle = html_entity_decode($img['title'], ENT_QUOTES, 'UTF-8');
                $this->xmlWriter->writeElement('image:title', $cleanTitle);
            }

            $this->xmlWriter->endElement(); // </image:image>
        }

        $this->xmlWriter->endElement(); // </url>
    }

    /**
     * Initializes a new XML file and opens the writer URI
     *
     * @param string $filename The name of the file being created.
     * @return void
     */
    private function startNewSitemapFile(string $filename): void
    {
        $this->xmlWriter = new XMLWriter();
        $this->sitemapIndexFiles[] = base_url($filename);

        $this->xmlWriter->openURI(FCPATH . $filename);
        $this->xmlWriter->setIndent(true);
        $this->xmlWriter->startDocument('1.0', 'UTF-8');

        $this->xmlWriter->startElement('urlset');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->xmlWriter->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $this->xmlWriter->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
    }

    /**
     * Finalizes the current XML document, flushes it to disk, and closes the writer
     *
     * @return void
     */
    private function finishCurrentSitemapFile(): void
    {
        if ($this->xmlWriter) {
            $this->xmlWriter->endElement(); // </urlset>
            $this->xmlWriter->endDocument();
            $this->xmlWriter->flush();
            $this->xmlWriter = null;
        }
    }

    /**
     * Generates the master sitemap index file that points to all chunked sitemap files
     *
     * @return void
     */
    private function generateSitemapIndex(): void
    {
        $writer = new XMLWriter();
        $writer->openURI(FCPATH . 'sitemap.xml');
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->sitemapIndexFiles as $url) {
            $writer->startElement('sitemap');
            $writer->writeElement('loc', $url);
            $writer->writeElement('lastmod', date('c'));
            $writer->endElement();
        }

        $writer->endElement(); // </sitemapindex>
        $writer->endDocument();
        $writer->flush();
    }

    /**
     * Deletes all existing XML sitemap files from the root directory
     *
     * @return void
     */
    public function deleteOldSitemaps(): void
    {
        $files = glob(FCPATH . 'sitemap*.xml');
        if ($files) {
            array_map('unlink', $files);
        }
    }

    /**
     * Initializes active system languages to construct Hreflang URLs correctly
     *
     * @return void
     */
    private function initLanguages(): void
    {
        $langs = getContextValue('languages');

        if (!empty($langs)) {
            foreach ($langs as $lang) {
                if (isset($lang->id, $lang->short_form)) {

                    $baseUrl = base_url($lang->short_form) . '/';

                    // Root directory is used for the default site language
                    if ((int)$lang->id === (int)$this->config->site_lang) {
                        $baseUrl = base_url();
                    }

                    $this->languages[$lang->id] = [
                        'short_form' => $lang->short_form,
                        'base_url'   => $baseUrl
                    ];
                }
            }
        }
    }
}