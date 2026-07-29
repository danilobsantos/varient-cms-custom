<?php

namespace App\Traits;

use Generator;
use RuntimeException;
use ValueError;

trait CsvImportTrait
{
    /**
     * Reads CSV file and yields associative arrays based on header.
     * Memory efficient (Generator).
     */
    protected function readCsv(string $filePath, bool $hasHeader = true, ?string $delimiter = null): Generator
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('CSV file not found or not readable.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Failed to open file stream.');
        }

        // Handle BOM (Byte Order Mark) for UTF-8 compatibility
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Auto-detect delimiter if not provided
        if ($delimiter === null) {
            $delimiter = $this->detectDelimiter($handle);
        }

        // HEADER PROCESSING
        $headers = [];
        if ($hasHeader) {
            // Note: Escape char is set to '\\' (Standard). PHP 8+ throws error if empty.
            $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');

            if (!empty($headers)) {
                // Sanitize headers: trim spaces and convert to lowercase
                $headers = array_map(fn($h) => strtolower(trim($h)), $headers);
            }
        }

        $rowNumber = $hasHeader ? 2 : 1;

        // ROW PROCESSING
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {

            // Skip completely empty rows (ignores rows with only null or empty strings)
            if ($row === [null] || empty(array_filter($row, fn($cell) => $cell !== null && trim($cell) !== ''))) {
                $rowNumber++;
                continue;
            }

            // Map values to headers if applicable
            if ($hasHeader && !empty($headers)) {
                $headerCount = count($headers);
                $rowCount = count($row);

                // Safety: Fix row column count mismatch
                if ($rowCount < $headerCount) {
                    $row = array_pad($row, $headerCount, ''); // Pad missing columns
                } elseif ($rowCount > $headerCount) {
                    $row = array_slice($row, 0, $headerCount); // Trim extra columns
                }

                // Combine headers with row data
                try {
                    $yieldData = array_combine($headers, $row);
                } catch (ValueError $e) {
                    // Skip row if combination fails unexpectedly
                    $rowNumber++;
                    continue;
                }
            } else {
                $yieldData = $row;
            }

            // Yield sanitized data (trim strings)
            yield $rowNumber => array_map(
                static fn($value) => is_string($value) ? trim($value) : $value,
                $yieldData
            );

            $rowNumber++;
        }

        fclose($handle);
    }

    /**
     * Detects the CSV delimiter (comma or semicolon).
     */
    protected function detectDelimiter($handle): string
    {
        $pos = ftell($handle);
        $line = fgets($handle);
        fseek($handle, $pos); // Reset pointer

        if (!$line) return ',';

        // Remove BOM and whitespaces to avoid false detection
        $line = ltrim($line, "\xEF\xBB\xBF \t");

        // Return semicolon if it appears more often than comma
        return (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
    }
}