<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Class VideoFetcher
 *
 * A robust library for fetching video metadata and embed URLs from various providers
 * (YouTube, Vimeo, Dailymotion, Facebook, Instagram). It utilizes standard OEmbed endpoints
 * where available and falls back to manual embed URL generation when APIs are restricted.
 */
class VideoFetcher
{
    /**
     * Connection timeout in seconds
     * @var int
     */
    private const CONNECT_TIMEOUT = 5;

    /**
     * Total execution timeout in seconds
     * @var int
     */
    private const EXEC_TIMEOUT = 10;

    /**
     * Fetches video metadata from a given URL using OEmbed or Fallbacks
     *
     * Workflow Details:
     * - Validates the input URL.
     * - Normalizes the URL (e.g., converts mobile links to desktop).
     * - Identifies the provider using Regex to avoid false positives.
     * - Intercepts Instagram URLs immediately for manual fallback due to strict API limits.
     * - Attempts to fetch data via standard OEmbed API endpoints.
     * - If OEmbed fails or isn't available (like Facebook), attempts provider-specific fallbacks.
     *
     * @param string $url The absolute video URL.
     * @return array Returns a standardized associative array with 'success' boolean,
     * and either a 'data' array containing video details or an 'error' message.
     */
    public function getInfo(string $url): array
    {
        if (empty($url)) {
            return ['success' => false, 'error' => 'URL is required.'];
        }

        // Normalize URL (Fix mobile links, etc.)
        $url = $this->normalizeUrl($url);

        // Identify Provider using Regex
        $provider = $this->identifyProvider($url);

        if ($provider === 'Unknown') {
            return ['success' => false, 'error' => 'Unsupported video provider or invalid URL.'];
        }

        // Special Handling for Instagram (Direct Fallback)
        if ($provider === 'Instagram') {
            return $this->getInstagramFallback($url);
        }

        // Try OEmbed First
        $oembedUrl = $this->getOembedUrl($url, $provider);

        if ($oembedUrl) {
            $response = $this->makeRequest($oembedUrl);

            if ($response['success']) {
                $data = json_decode((string)$response['body'], true);

                if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
                    $embedUrl = '';

                    // Extract src from the OEmbed HTML iframe
                    if (!empty($data['html']) && preg_match('/src=["\']([^"\']+)["\']/i', $data['html'], $matches)) {
                        $embedUrl = $matches[1];
                    }

                    // Handle YouTube high-quality thumbnails without black bars
                    $thumbnailUrl = $data['thumbnail_url'] ?? '';
                    if ($provider === 'YouTube') {
                        $thumbnailUrl = $this->getYouTubeThumbnail($url, $thumbnailUrl);
                    }

                    return [
                        'success' => true,
                        'data'    => [
                            'title'       => $data['title'] ?? ucfirst($provider) . ' Video',
                            'description' => $data['description'] ?? '',
                            'image'       => $thumbnailUrl,
                            'embed_code'  => $embedUrl,
                            'provider'    => $data['provider_name'] ?? $provider,
                            'url'         => $url
                        ]
                    ];
                }
            }
        }

        // If OEmbed failed or not available, try Provider-Specific Fallbacks
        if ($provider === 'Facebook') {
            return $this->getFacebookFallback($url);
        }

        return ['success' => false, 'error' => 'Could not fetch video data.'];
    }

    /**
     * Attempts to get the highest resolution YouTube thumbnail without black bars.
     * Falls back to the provided default if maxresdefault is not available.
     *
     * @param string $url The YouTube video URL.
     * @param string $default The default thumbnail from OEmbed.
     * @return string Returns the highest available resolution thumbnail URL.
     */
    private function getYouTubeThumbnail(string $url, string $default): string
    {
        $videoId = '';
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $videoId = $match[1];
        }

        if (!$videoId) {
            return $default;
        }

        $maxResUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";

        // Check if maxresdefault exists using a lightweight HEAD request
        $ch = curl_init($maxResUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($retcode === 200) ? $maxResUrl : $default;
    }

    /**
     * Normalizes URLs to ensure compatibility with OEmbed endpoints
     *
     * @param string $url The raw video URL.
     * @return string The normalized video URL.
     */
    private function normalizeUrl(string $url): string
    {
        // Fix Facebook mobile links
        if (strpos($url, 'm.facebook.com') !== false) {
            $url = str_replace('m.facebook.com', 'www.facebook.com', $url);
        }
        return $url;
    }

    /**
     * Identifies the video provider based on the URL using strict Regex patterns
     *
     * @param string $url The normalized video URL.
     * @return string The identified provider name (e.g., 'YouTube', 'Facebook'), or 'Unknown'.
     */
    private function identifyProvider(string $url): string
    {
        if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
            return 'YouTube';
        }

        if (preg_match('/(vimeo\.com)/i', $url)) {
            return 'Vimeo';
        }

        if (preg_match('/(dailymotion\.com|dai\.ly)/i', $url)) {
            return 'Dailymotion';
        }

        if (preg_match('/(facebook\.com|fb\.watch)/i', $url)) {
            return 'Facebook';
        }

        if (preg_match('/(instagram\.com|instagr\.am)/i', $url)) {
            return 'Instagram';
        }

        return 'Unknown';
    }

    /**
     * Generates the appropriate OEmbed API URL for the identified provider
     *
     * @param string $url The encoded video URL to be queried.
     * @param string $provider The name of the video provider.
     * @return string|null Returns the absolute OEmbed API URL, or null if unsupported.
     */
    private function getOembedUrl(string $url, string $provider): ?string
    {
        $encodedUrl = urlencode($url);

        switch ($provider) {
            case 'YouTube':
                return "https://www.youtube.com/oembed?url={$encodedUrl}&format=json";

            case 'Vimeo':
                return "https://vimeo.com/api/oembed.json?url={$encodedUrl}";

            case 'Dailymotion':
                return "https://www.dailymotion.com/services/oembed?url={$encodedUrl}";
        }

        return null;
    }

    /**
     * Manual fallback generation for Facebook videos
     *
     * @param string $url The Facebook video URL.
     * @return array Returns a standardized successful response array containing the embed URL.
     */
    private function getFacebookFallback(string $url): array
    {
        $encodedUrl = urlencode($url);
        // Using the plugins/video.php endpoint to get the direct embed link
        $embedUrl = "https://www.facebook.com/plugins/video.php?href={$encodedUrl}&show_text=false&width=560";

        return [
            'success' => true,
            'data'    => [
                'title'       => 'Facebook Video',
                'description' => '',
                'image'       => '', // No thumbnail without API
                'embed_code'  => $embedUrl,
                'provider'    => 'Facebook',
                'url'         => $url
            ]
        ];
    }

    /**
     * Manual fallback generation for Instagram posts/reels
     *
     * @param string $url The Instagram post/reel URL.
     * @return array Returns a standardized successful response array containing the embed URL.
     */
    private function getInstagramFallback(string $url): array
    {
        // Remove query parameters to clean up the URL for embed
        $cleanUrl = strtok($url, '?');

        // Construct the direct embed URL
        $embedUrl = rtrim($cleanUrl, '/') . '/embed';

        return [
            'success' => true,
            'data'    => [
                'title'       => 'Instagram Post',
                'description' => '',
                'image'       => '',
                'embed_code'  => $embedUrl,
                'provider'    => 'Instagram',
                'url'         => $url
            ]
        ];
    }

    /**
     * Executes a secure cURL request with configured timeouts and error handling
     *
     * @param string $url The target API URL to request.
     * @return array Returns an associative array with 'success' status, and either 'body' or 'error'.
     */
    private function makeRequest(string $url): array
    {
        $ch = curl_init();

        // Environment Check for SSL
        $isProduction = getenv('CI_ENVIRONMENT') === 'production';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Security: Verify Peer in Production, optional in Dev
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isProduction);

        // Timeouts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::EXEC_TIMEOUT);

        // User Agent (Mimic a browser to avoid some strict API blocks)
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; VideoFetcher/2.0; +https://yoursite.com)');

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($body === false) {
            return ['success' => false, 'error' => 'cURL Error: ' . $error];
        }

        if ($httpCode >= 400) {
            return ['success' => false, 'error' => 'HTTP Error Code: ' . $httpCode];
        }

        return ['success' => true, 'body' => $body];
    }
}