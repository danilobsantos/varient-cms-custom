<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PostModel;
use App\Traits\CsvAnalyzeTrait;
use App\Traits\CsvImportTrait;
use Exception;
use Throwable;

/**
 * Class PostImportService
 *
 * Handles the analysis and batch importation of posts via CSV files.
 * Integrates image downloading, tag synchronization, and permission checks.
 */
class PostImportService
{
    use CsvImportTrait;
    use CsvAnalyzeTrait;

    /**
     * Global application configuration object
     * * @var object|null
     */
    protected ?object $config;

    /**
     * List of permitted post formats
     * * @var array<int, string>
     */
    protected array $allowedPostFormats = ['article', 'video', 'audio', 'gallery', 'poll'];

    /**
     * PostImportService constructor
     */
    public function __construct()
    {
        $this->config = getContextValue('config');
    }

    /**
     * Analyzes the CSV file: Counts total rows and validates the structure
     *
     * @param string $filePath The absolute path to the CSV file.
     * @return int The total number of valid rows found.
     */
    public function analyzeFile(string $filePath): int
    {
        return $this->validateAndCountCsv($filePath, ['title']);
    }

    /**
     * Processes a specific batch of rows from the CSV file
     *
     * @param string $filePath The absolute path to the CSV file.
     * @param int $offset The row index to start processing from.
     * @param int $limit The maximum number of rows to process in this batch.
     * @param string $publishStatus The intended publish status ('publish', 'draft', 'scheduled').
     * @param string|null $scheduledAt The scheduled publish date string, if applicable.
     * @param int $userId The ID of the user performing the import.
     * @return array{processed: int, errors: array<int, string>} Processing results containing processed count and error messages.
     */
    public function importBatch(string $filePath, int $offset, int $limit, string $publishStatus, ?string $scheduledAt, int $userId): array
    {
        $generator = $this->readCsv($filePath);

        $successCount = 0;
        $rowsProcessed = 0;
        $errors = [];
        $currentIndex = 0;

        foreach ($generator as $rowNumber => $rowData) {
            // Pagination Check: Skip rows until reaching the offset
            if ($currentIndex < $offset) {
                $currentIndex++;
                continue;
            }

            // Limit Check: Stop processing once the batch limit is reached
            if ($rowsProcessed >= $limit) {
                break;
            }

            $rowsProcessed++;

            try {
                if (empty($rowData) || !is_array($rowData)) {
                    $currentIndex++;
                    continue;
                }

                // Normalize keys to lowercase for consistent mapping
                $rowData = array_change_key_case($rowData, CASE_LOWER);

                $this->processRow($rowData, $publishStatus, $scheduledAt, $userId);

                $successCount++;

            } catch (Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }

            $currentIndex++;
        }

        return [
            'processed' => $rowsProcessed,
            'errors'    => $errors
        ];
    }

    /**
     * Maps a single CSV row to a Post Entity and saves it to the database
     *
     * @param array<string, mixed> $csvRow The associative array representing a CSV row.
     * @param string $publishStatus The intended publish status.
     * @param string|null $scheduledAt The scheduled datetime string.
     * @param int $userId The ID of the author.
     * @return void
     * @throws Exception If mandatory columns are missing or validation fails.
     */
    private function processRow(array $csvRow, string $publishStatus, ?string $scheduledAt, int $userId): void
    {
        if (empty($csvRow['title'])) {
            throw new Exception("The 'title' column is missing or empty.");
        }

        $postModel = new PostModel();
        $post = new \App\Entities\Post();

        // Prepare data map for the entity
        $formData = [
            'lang_id'           => (int)($csvRow['lang_id'] ?? 1),
            'title'             => (string)($csvRow['title'] ?? ''),
            'slug'              => (string)($csvRow['slug'] ?? ''),
            'summary'           => (string)($csvRow['summary'] ?? ''),
            'content'           => (string)($csvRow['content'] ?? ''),
            'category_id'       => (int)($csvRow['category_id'] ?? 1),
            'post_format'       => (string)($csvRow['post_format'] ?? 'article'),
            'video_embed_code'  => (string)($csvRow['video_embed_code'] ?? ''),
            'image_description' => (string)($csvRow['image_description'] ?? ''),
            'meta_title'        => (string)($csvRow['meta_title'] ?? ''),
            'meta_description'  => (string)($csvRow['meta_description'] ?? ''),
            'meta_keywords'     => (string)($csvRow['meta_keywords'] ?? ''),
            'user_id'           => $userId,
        ];

        // Process remote image if provided
        if (!empty($csvRow['image_url'])) {
            $imageId = $this->processImage((string)$csvRow['image_url'], $userId);
            $formData['image_id'] = $imageId ?? null;
        }

        // Fill Entity with mapped data
        $post->fill($formData);

        // Run internal Entity business logic
        $defaultLangId = 1;
        $post->handlePostForm($formData, $this->allowedPostFormats, $defaultLangId, $userId);

        // Set visibility based on user permissions and site configuration
        $post->visibility = 0;

        // Null-safe operator ensures no fatal errors if config is missing
        if (hasPermission('manage_all_posts') || (int)($this->config?->require_approval_new_posts ?? 0) !== 1) {
            $post->visibility = 1;
        }

        // Handle publish status logic
        if ($publishStatus === 'draft') {
            $post->status = 0;
        }

        if ($publishStatus === 'scheduled' && !empty($scheduledAt)) {
            $post->is_scheduled = 1;
            $post->status = 1;
            $post->scheduled_at = date('Y-m-d H:i:s', strtotime($scheduledAt));
        }

        // Attempt to save the entity into the database
        if (!$postModel->trySave($post)) {
            $validationErrors = implode(', ', $postModel->errors());
            throw new Exception("Validation Error: " . $validationErrors);
        }

        // Post-save actions (Slug generation & ID retrieval)
        $postId = $postModel->getInsertID();
        model('PageModel')->updateSlug('posts', $postId);

        // Sync tags if they exist in the CSV row
        if (!empty($csvRow['tags'])) {

            // Set ID
            $post->id = $postId;

            // Strict casting to string prevents TypeError in explode
            $tagsArray = explode(',', (string)$csvRow['tags']);

            $tags = implode(',', array_unique(array_filter(array_map(
                fn($t) => trim(strip_tags((string)$t)),
                $tagsArray
            ))));

            model('TagModel')->syncTagsForPost($post, $tags);
        }
    }

    /**
     * Downloads an image from a URL, processes it, and registers it in the database
     *
     * @param string|null $imageUrl The remote URL of the image.
     * @param int $userId The ID of the user importing the posts.
     * @return int|null The inserted image ID on success, or null on failure.
     */
    private function processImage(?string $imageUrl, int $userId): ?int
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            $downloadService = new \App\Services\DownloadService();
            $tempFileName = $downloadService->downloadImage($imageUrl);

            if (!$tempFileName) {
                return null;
            }

            $tempFile = [
                'path'         => 'uploads/temp/' . $tempFileName,
                'originalName' => $tempFileName
            ];

            $uploadService = new \App\Services\UploadService($this->config);
            $uploadedImage = $uploadService->uploadPostImage(null, $tempFile);

            if (empty($uploadedImage)) {
                return null;
            }

            $imageModel = model('ImageModel');
            $image = $imageModel->create($uploadedImage, $userId);

            return !empty($image) ? (int)$image->id : null;

        } catch (Throwable $e) {
            log_message('error', '[PostImportService] Image processing failed: ' . $e->getMessage());
            return null;
        }
    }
}