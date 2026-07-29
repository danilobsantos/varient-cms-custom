<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/simplepie/vendor/autoload.php";

use SimplePie\SimplePie;
use SimplePie\Item;
use DOMDocument;

/**
 * RssParser Library
 *
 * A robust, stateless RSS & Atom feed aggregator wrapper for CodeIgniter 4.
 * Built on SimplePie, designed for high-security and modern web environments.
 */
class RssParser
{
    /**
     * @var string The absolute path where RSS feed cache files are stored
     */
    private string $cachePath;

    /**
     * RssParser Constructor
     */
    public function __construct()
    {
        $this->cachePath = WRITEPATH . 'cache/rss_feed';

        // Ensure cache directory exists with secure permissions (0755)
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Fetch and parse feed items from a given URL
     *
     * @param string $url           The RSS or Atom feed URL to parse.
     * @param int    $limit         The maximum number of items to retrieve.
     * @param int    $cacheDuration The cache lifetime in seconds (default is 15 minutes).
     * @return array Returns an array of standardized feed item associative arrays.
     */
    public function fetch(string $url, int $limit = 20, int $cacheDuration = 900): array
    {
        $feed = new SimplePie();
        $feed->set_cache_location($this->cachePath);

        // Imitate Chrome to bypass WAF/Cloudflare
        $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        // Aggressive connection settings for shared hosting
        $feed->force_fsockopen(false);
        $feed->set_curl_options([
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15
        ]);

        $feed->strip_comments(true);
        $feed->set_feed_url($url);
        $feed->set_cache_duration($cacheDuration);
        $feed->enable_cache($cacheDuration > 0);

        if (!$feed->init()) {
            log_message('error', "[RSS Error] URL: {$url} | " . $feed->error());
            return [];
        }

        $feed->handle_content_type();
        $items = [];

        foreach ($feed->get_items(0, $limit) as $item) {
            $items[] = [
                'guid'        => $this->getReliableGuid($item),
                'title'       => $this->cleanText($item->get_title()),
                'permalink'   => $item->get_permalink(),
                'description' => $this->cleanText($item->get_description()),
                'content'     => $item->get_content(),
                'date'        => dateTimeNow(),
                'image_url'   => $this->extractImage($item)
            ];
        }

        return $items;
    }

    /**
     * Generate a reliable unique ID for a feed item
     *
     * @param Item $item The parsed SimplePie item.
     * @return string The unique identifier.
     */
    private function getReliableGuid(Item $item): string
    {
        return $item->get_id() ?: $item->get_permalink();
    }

    /**
     * Image extraction "Waterfall" strategy
     *
     * Attempts to locate the best representative image for the feed item by sequentially
     * checking Media RSS tags, enclosures, iTunes extensions, and finally parsing the HTML content.
     * Validates and normalizes the resulting URL.
     *
     * @param Item $item The parsed SimplePie item.
     * @return string|null Returns the absolute image URL, or null if no valid image is found.
     */
    private function extractImage(Item $item): ?string
    {
        $imageUrl = null;

        // Media RSS (Content)
        $mediaContent = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
        if (isset($mediaContent[0]['attribs']['']['url'])) {
            $imageUrl = $mediaContent[0]['attribs']['']['url'];
        }

        // Media RSS (Thumbnail)
        if (empty($imageUrl)) {
            $mediaThumb = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
            if (isset($mediaThumb[0]['attribs']['']['url'])) {
                $imageUrl = $mediaThumb[0]['attribs']['']['url'];
            }
        }

        // Enclosure
        if (empty($imageUrl) && ($enclosure = $item->get_enclosure())) {
            if ($enclosure->get_link() && strpos((string)$enclosure->get_type(), 'image') !== false) {
                $imageUrl = $enclosure->get_link();
            }
        }

        // Custom <image> tag (Non-standard)
        if (empty($imageUrl)) {
            $customImage = $item->get_item_tags('', 'image');
            if (isset($customImage[0]['data'])) {
                $imageUrl = trim($customImage[0]['data']);
            }
        }

        // iTunes Tags
        if (empty($imageUrl)) {
            $itunesImage = $item->get_item_tags('http://www.itunes.com/dtds/podcast-1.0.dtd', 'image');
            if (isset($itunesImage[0]['attribs']['']['href'])) {
                $imageUrl = $itunesImage[0]['attribs']['']['href'];
            }
        }

        // DOMDocument Fallback (Parse HTML body)
        if (empty($imageUrl)) {
            $htmlContent = $item->get_content();
            if (!empty($htmlContent)) {
                $imageUrl = $this->extractImageFromHtml($htmlContent);
            }
        }

        // Final Validation & Protocol Fix
        if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            if (strpos($imageUrl, 'http://') === 0) {
                $imageUrl = substr_replace($imageUrl, 'https://', 0, 7);
            }
            return $imageUrl;
        }

        return null;
    }

    /**
     * Extract image from HTML using DOMDocument
     *
     * @param string $html The raw HTML content from the feed item.
     * @return string|null The source URL of the best candidate image, or null if none found.
     */
    private function extractImageFromHtml(string $html): ?string
    {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        // Force UTF-8 encoding for special characters
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        if (empty($html) || !$doc->loadHTML($html)) {
            libxml_clear_errors();
            return null;
        }

        $foundUrl = null;
        foreach ($doc->getElementsByTagName('img') as $img) {
            // Check Lazy-Load attributes first
            $src = $img->getAttribute('data-src')
                ?: $img->getAttribute('data-original')
                    ?: $img->getAttribute('src');

            if (empty($src)) continue;

            // Filter tracking pixels and ads
            if (
                stripos($src, 'feedburner') !== false ||
                stripos($src, 'pixel') !== false ||
                stripos($src, 'tracker') !== false ||
                stripos($src, 'adserver') !== false ||
                stripos($src, 'doubleclick') !== false ||
                stripos($src, 'emoji') !== false ||
                stripos($src, 'smilie') !== false
            ) {
                continue;
            }

            // Skip 1x1 tracking pixels
            $width = $img->getAttribute('width');
            $height = $img->getAttribute('height');
            if ($width === '1' && $height === '1') {
                continue;
            }

            $foundUrl = $src;
            break;
        }

        libxml_clear_errors();
        return $foundUrl;
    }

    /**
     * Clean text while preserving basic word spacing
     *
     * @param string|null $text The raw string to be cleaned.
     * @return string The sanitized, plain-text string.
     */
    private function cleanText(?string $text): string
    {
        if ($text === null) return '';

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Convert block tags to spaces to prevent word concatenation
        $text = str_replace(['<br>', '<br/>', '</p>', '</div>', '</h1>', '</h2>'], ' ', $text);

        return trim(strip_tags($text));
    }
}