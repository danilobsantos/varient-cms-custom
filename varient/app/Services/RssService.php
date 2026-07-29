<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\RssParser;
use App\Entities\Post;
use Exception;
use Throwable;

/**
 * Class RssService
 *
 * Handles the fetching, parsing, and safe importation of RSS feeds into the database.
 * Supports automated metadata generation, image downloading, and duplicate prevention.
 */
class RssService
{
    /**
     * @var object The model responsible for Post database operations
     */
    protected object $postModel;

    /**
     * RssService constructor
     */
    public function __construct()
    {
        $this->postModel = model('PostModel');
    }

    /**
     * Imports a single RSS feed URL into the database using Post Entities
     *
     * @param object $feed The feed configuration object containing URL, limits, and settings.
     * @return array{added: int, errors: int, messages: array<int, string>} The statistics of the import process.
     */
    public function importFeed(object $feed): array
    {
        $rssLib = new RssParser();

        // Safe casting to integer to prevent TypeErrors in the fetch method
        $limit = (int)($feed->post_limit ?? 10);
        $items = $rssLib->fetch((string)$feed->feed_url, $limit);

        if (empty($items)) {
            return ['added' => 0, 'errors' => 0, 'messages' => ['No items found.']];
        }

        $stats = [
            'added'    => 0,
            'errors'   => 0,
            'messages' => []
        ];

        $pageModel = model('PageModel');
        $counter = 0;

        foreach ($items as $item) {

            if ($counter >= $limit) {
                break;
            }

            $guidHash = $this->generateGuid($item);

            // Skip if fingerprint could not be generated or post already exists
            if (empty($guidHash) || $this->postModel->where('guid', $guidHash)->first()) {
                continue;
            }

            try {
                // Safe string casting to prevent array-injection from malformed RSS nodes
                $title = (string)($item['title'] ?? '');
                $content = (string)($item['content'] ?? '');

                $description = strip_tags((string)($item['description'] ?? ''));
                if (empty($description)) {
                    $description = word_limiter(strip_tags($content), 30);
                }

                $imageUrl = (string)($item['image_url'] ?? '');

                $post = new Post();

                // Assign raw values
                $post->lang_id = (int)$feed->lang_id;
                $post->title = $title;
                $post->slug = !empty($title) ? strSlug($title) : 'post-' . uniqid();
                $post->guid = $guidHash;
                $post->content = $content;
                $post->summary = $description;
                $post->image_url = $imageUrl;
                $post->post_url = (string)($item['permalink'] ?? '');
                $post->category_id = (int)$feed->category_id;
                $post->post_format = 'article';
                $post->user_id = (int)$feed->user_id;
                $post->feed_id = (int)$feed->id;
                $post->show_post_url = (int)($feed->read_more_button ?? 0);
                $post->status = (isset($feed->add_posts_as_draft) && (int)$feed->add_posts_as_draft === 1) ? 0 : 1;
                $post->created_at = dateTimeNow();

                // Handle auto-generation of keywords
                if (isset($feed->generate_keywords_from_title) && (int)$feed->generate_keywords_from_title === 1) {
                    $post->meta_keywords = generateKeywords($post->title);
                }

                // Handle external image downloading and local saving
                if (isset($feed->image_saving_method) && $feed->image_saving_method === 'download') {
                    $imageId = $this->processImages($imageUrl, (int)$feed->user_id);
                    if (!empty($imageId)) {
                        $post->image_id = $imageId;
                        $post->image_url = ''; // Clear remote URL if saved locally
                    }
                }

                $insertID = null;
                if ($this->postModel->trySave($post)) {
                    $insertID = $this->postModel->getInsertID();
                }

                if (!$insertID) {
                    $errors = $this->postModel->errors();
                    throw new Exception(implode(', ', $errors));
                }

                // Update page routing slug
                $pageModel->updateSlug('posts', $insertID);

                $stats['added']++;
                $counter++;

            } catch (Exception $e) {
                $stats['errors']++;
                $stats['messages'][] = "Error '{$item['title']}': " . $e->getMessage();
                log_message('error', '[RSS Import] ' . $e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Generates a unique MD5 hash fingerprint to prevent duplicate RSS entries
     *
     * @param array $item The parsed RSS item array.
     * @return string|null The generated MD5 hash, or null if generation fails.
     */
    private function generateGuid(array $item): ?string
    {
        $rawString = (string)($item['guid'] ?? '');

        if (empty($rawString)) {
            $rawString = (string)($item['permalink'] ?? '');
        }

        if (empty($rawString)) {
            $title = (string)($item['title'] ?? '');
            $date = (string)($item['date'] ?? '');

            $fingerprint = $title . $date;

            if (!empty($fingerprint)) {
                $rawString = $fingerprint;
            }
        }

        if (empty($rawString)) {
            return null;
        }

        return md5($rawString);
    }

    /**
     * Downloads a remote image from an RSS feed and registers it in the local system
     *
     * @param string|null $imageUrl The remote URL of the image.
     * @param int $userId The ID of the user assigned to the imported post.
     * @return int|null The local database ID of the downloaded image, or null on failure.
     */
    private function processImages(?string $imageUrl, int $userId): ?int
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            $downloadService = new DownloadService();
            $tempFileName = $downloadService->downloadImage($imageUrl);

            if (!$tempFileName) {
                return null;
            }

            $tempFile = [
                'path'         => 'uploads/temp/' . $tempFileName,
                'originalName' => $tempFileName
            ];

            $config = getContextValue('config');
            $uploadService = new UploadService($config);
            $uploadedImage = $uploadService->uploadPostImage(null, $tempFile);

            if (empty($uploadedImage)) {
                return null;
            }

            $imageModel = model('ImageModel');
            $image = $imageModel->create($uploadedImage, $userId);

            return !empty($image) ? (int)$image->id : null;

        } catch (Throwable $e) {
            log_message('error', '[RSS Import] Image processing failed: ' . $e->getMessage());
            return null;
        }
    }
}