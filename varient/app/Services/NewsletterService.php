<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\CsvExportTrait;
use App\Traits\CsvImportTrait;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/**
 * Class NewsletterService
 *
 * Handles exporting and importing newsletter emails via CSV files
 * Uses batch processing to handle large datasets efficiently without exhausting memory
 */
class NewsletterService
{
    use CsvExportTrait;
    use CsvImportTrait;

    protected object $newsletterModel;

    /**
     * NewsletterService constructor
     */
    public function __construct()
    {
        $this->newsletterModel = model('NewsletterModel');
    }

    /**
     * Exports all emails to a CSV file and streams the download directly to the browser
     *
     * @return void
     */
    public function export(): void
    {
        // Fetch data query builder for streaming
        $query = $this->newsletterModel->getAllForExport();

        // Column mapping (DB Column => CSV Header)
        $columnMapping = [
            'id'         => (string)trans("id"),
            'email'      => (string)trans("email"),
            'created_at' => (string)trans("date"),
        ];

        $filename = 'newsletter_' . dateTimeNow();

        // Stream download to avoid loading entire table into memory
        $this->streamCsv($query, $columnMapping, $filename);
    }

    /**
     * Imports emails from a provided CSV file in batches
     *
     * @param UploadedFile|null $file The uploaded CSV file object.
     * @return array{success: int, error: int, skipped: int} Statistics of the import process.
     * @throws RuntimeException If the file is missing, invalid, or has a wrong extension.
     */
    public function import(?UploadedFile $file): array
    {
        // Validate file presence, validity, and extension strictly
        if ($file === null || !$file->isValid() || $file->getExtension() !== 'csv') {
            throw new RuntimeException((string)trans("msg_invalid_file"));
        }

        $tempPath = (string)$file->getTempName();
        $stats = ['success' => 0, 'error' => 0, 'skipped' => 0];

        try {
            $batchSize = 500;
            $batchData = [];

            // Read CSV with headers enabled (Trait handles normalization via Generator)
            foreach ($this->readCsv($tempPath, true) as $row) {

                // Safety check: ensure the yielded row is a valid array
                if (!is_array($row)) {
                    $stats['error']++;
                    continue;
                }

                $email = isset($row['email']) ? trim((string)$row['email']) : '';

                // Validate email format strictly
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $stats['error']++;
                    continue;
                }

                // Prepare data array for bulk insertion
                $batchData[] = [
                    'email'      => $email,
                    'token'      => generateSecureToken(),
                    'created_at' => dateTimeNow()
                ];

                // Process batch if the buffer is full to flush memory
                if (count($batchData) >= $batchSize) {
                    $this->processBatch($batchData, $stats);
                    $batchData = []; // Reset batch buffer
                }
            }

            // Process any remaining records left in the buffer
            if (!empty($batchData)) {
                $this->processBatch($batchData, $stats);
            }

        } finally {
            // Ensure the temporary file is always deleted, even if an exception is thrown
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return $stats;
    }

    /**
     * Helper method to process a chunk of email data
     *
     * @param array<int, array<string, string>> $batchData The array of email records to insert.
     * @param array<string, int>                &$stats The statistics array passed by reference.
     * @return void
     */
    protected function processBatch(array $batchData, array &$stats): void
    {
        $emails = array_column($batchData, 'email');

        // Check for existing emails in the database to prevent duplicate keys
        $existingEmails = $this->newsletterModel
            ->whereIn('email', $emails)
            ->findColumn('email');

        // findColumn returns null if no records are found; fallback to an empty array
        if (!is_array($existingEmails)) {
            $existingEmails = [];
        }

        // Filter out duplicate emails
        $newRecords = [];
        foreach ($batchData as $row) {
            // Strict comparison in array search to avoid type coercion bugs
            if (in_array((string)$row['email'], $existingEmails, true)) {
                $stats['skipped']++;
            } else {
                $newRecords[] = $row;
            }
        }

        // Execute bulk insert for completely new records
        if (!empty($newRecords)) {
            $this->newsletterModel->builder()->insertBatch($newRecords);
            $stats['success'] += count($newRecords);
        }
    }
}