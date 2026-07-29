<?php

namespace App\Traits;

trait CsvExportArrayTrait
{
    /**
     * Streams an array of data directly to the browser as a CSV file.
     *
     * @param array $data The multi-dimensional array containing the data to export.
     * @param array $columnMapping Associative array mapping data keys to CSV headers.
     * Format: ['array_key' => 'Display Header Name']
     * @param string $filename The desired name for the downloaded file.
     * @return void Exits the script execution after streaming.
     */
    protected function streamArrayCsv(array $data, array $columnMapping, string $filename): void
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

        // Add UTF-8 BOM (Byte Order Mark) for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Define Delimiter
        $delimiter = ',';

        // Write CSV Headers
        fputcsv($output, array_values($columnMapping), $delimiter);

        // Prepare Data Field Keys
        $dataKeys = array_keys($columnMapping);

        // Iterate & Stream Data
        foreach ($data as $row) {
            $orderedRow = [];

            foreach ($dataKeys as $field) {
                // Use null coalescing operator to handle missing keys gracefully
                $value = $row[$field] ?? '';

                // Prevent Excel Formula Injection (CSV Injection)
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