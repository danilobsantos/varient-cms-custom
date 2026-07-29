<?php

namespace App\Traits;

use Exception;

trait CsvAnalyzeTrait
{
    /**
     * Validates CSV structure, checks required columns, and counts rows
     * Uses strict validation logic
     *
     * @param string $filePath
     * @param array $requiredColumns
     * @return int Total valid row count
     * @throws Exception
     */
    public function validateAndCountCsv(string $filePath, array $requiredColumns = []): int
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new Exception("The file is not readable or does not exist.");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Could not open file stream.");
        }

        // Handle BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect Delimiter (Uses method from CsvImportTrait if available, else defaults)
        if (method_exists($this, 'detectDelimiter')) {
            $delimiter = $this->detectDelimiter($handle);
        } else {
            $delimiter = ','; // Fallback
        }

        // Validate Headers
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers || count($headers) < 1) {
            fclose($handle);
            throw new Exception("Invalid CSV format. Could not detect headers.");
        }

        // Normalize headers
        $cleanHeaders = array_map(function ($h) {
            return strtolower(trim($h));
        }, $headers);

        // Check required columns
        foreach ($requiredColumns as $col) {
            if (!in_array(strtolower($col), $cleanHeaders)) {
                fclose($handle);
                $foundHeaders = implode(', ', $headers);
                throw new Exception("Missing required column: '{$col}'. Found: [{$foundHeaders}].");
            }
        }

        // Validate Rows
        $rowCount = 0;
        $expectedColumnCount = count($headers);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Skip empty rows
            if ($row === [null] || empty(array_filter($row, fn($cell) => $cell !== null && trim($cell) !== ''))) {
                continue;
            }

            $currentRowNum = $rowCount + 2; // +1 header, +1 zero-index

            // Strict Column Count Check
            if (count($row) !== $expectedColumnCount) {
                fclose($handle);
                throw new Exception("Structure error at Row {$currentRowNum}. Expected {$expectedColumnCount} columns, found " . count($row) . ".");
            }

            $rowCount++;
        }

        fclose($handle);

        if ($rowCount === 0) {
            throw new Exception("The CSV file contains a header but no data rows.");
        }

        return $rowCount;
    }
}