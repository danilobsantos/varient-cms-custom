<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;

/**
 * Class DownloadService
 *
 * Handles fetching, validating, and safely saving remote images to the local filesystem.
 */
class DownloadService
{
    /**
     * Maximum allowed file size in bytes (Default: 100MB).
     * * @var int
     */
    private int $maxFileSize = 50 * 1024 * 1024;

    /**
     * Downloads an image from a remote URL, validates it, and saves it locally
     *
     * @param string $url    The remote image URL.
     * @param string $folder The subfolder inside 'uploads/' to save the file.
     * @param string $prefix The prefix for the generated filename.
     * @return string|false  Returns the generated filename (e.g., 'avatar_xyz.jpg') or false on failure.
     */
    public function downloadImage(string $url, string $folder = 'temp', string $prefix = 'img_'): string|false
    {
        // Validate URL format
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Prepare Upload Directory
        $uploadPath = rtrim(FCPATH . 'uploads/' . trim($folder, '/'), '/') . '/';

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true) && !is_dir($uploadPath)) {
                log_message('error', '[DownloadService] Failed to create directory: ' . $uploadPath);
                return false;
            }
        }

        // Initialize cURL
        $ch = curl_init($url);

        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS=> CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Validate cURL Response
        if ($curlErrno || empty($data) || !is_string($data) || ($info['http_code'] ?? 0) !== 200) {
            log_message('error', '[DownloadService] Download failed. Error: ' . $curlError . ' | HTTP Code: ' . ($info['http_code'] ?? 'N/A'));
            return false;
        }

        // Check File Size (Memory Safety)
        if (strlen($data) > $this->maxFileSize) {
            log_message('error', '[DownloadService] File size exceeds limit. Size: ' . strlen($data));
            return false;
        }

        // Validate Binary Data (Magic Bytes Check)
        try {
            $imageInfo = @getimagesizefromstring($data);
        } catch (Throwable $e) {
            $imageInfo = false;
        }

        if ($imageInfo === false || !isset($imageInfo['mime'])) {
            log_message('error', '[DownloadService] Downloaded data is not a valid image.');
            return false;
        }

        // Detect Extension from MIME Type
        $ext = $this->extensionFromMime($imageInfo['mime']);

        if (!$ext) {
            $ext = 'jpg';
        }

        // Generate Unique Filename
        $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
        $fullPath = $uploadPath . $filename;

        // Save to Disk
        if (file_put_contents($fullPath, $data) === false) {
            log_message('error', '[DownloadService] Failed to write file to disk: ' . $fullPath);
            return false;
        }

        return $filename;
    }

    /**
     * Maps MIME types to file extensions
     *
     * @param string $mime The MIME type extracted from the image.
     * @return string|null The file extension or null if unknown.
     */
    private function extensionFromMime(string $mime): ?string
    {
        $map = [
            'image/jpeg'    => 'jpg',
            'image/pjpeg'   => 'jpg',
            'image/png'     => 'png',
            'image/gif'     => 'gif',
            'image/webp'    => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp'     => 'bmp',
            'image/x-icon'  => 'ico',
        ];

        return $map[$mime] ?? null;
    }
}