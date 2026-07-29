<?php

namespace App\Traits;

use CodeIgniter\Database\ResultInterface;

trait CsvExportTrait
{
    /**
     * Streams a database query result directly to the browser as a CSV file
     *
     * @param ResultInterface $queryResult The unbuffered query result (cursor)
     * @param array $columnMapping Associative array mapping DB columns to CSV headers
     * Format: ['db_field_name' => 'Display Header Name']
     * @param string $filename The desired name for the downloaded file
     * @return void Exits the script execution after streaming
     */
    protected function streamCsv(ResultInterface $queryResult, array $columnMapping, string $filename): void
    {
        // Output Buffer Cleaning
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Filename Sanitization
        if (!str_ends_with($filename, '.csv')) {
            $filename .= '.csv';
        }

        // Set HTTP Headers for Download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open Output Stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM (Byte Order Mark)
        fwrite($output, "\xEF\xBB\xBF");

        // Define Delimiter
        $delimiter = ',';

        // Write CSV Headers
        fputcsv($output, array_values($columnMapping), $delimiter);

        // Prepare Database Field Keys
        $dbFields = array_keys($columnMapping);

        // Iterate & Stream Data
        while ($row = $queryResult->getUnbufferedRow('array')) {
            $orderedRow = [];

            foreach ($dbFields as $field) {
                // Use null coalescing operator to handle missing keys gracefully
                $value = $row[$field] ?? '';

                // Prevent Excel Formula Injection (CSV Injection)
                // If a cell value starts with specific characters (=, +, -, @),
                if (is_string($value) && preg_match('/^[=\-+@]/', $value)) {
                    $value = "'" . $value;
                }

                $orderedRow[] = $value;
            }

            // Write the sanitized row to the output stream
            fputcsv($output, $orderedRow, $delimiter);
        }

        // Close Stream and Exit
        fclose($output);
        exit;
    }
}