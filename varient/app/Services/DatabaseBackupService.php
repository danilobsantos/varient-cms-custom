<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class DatabaseBackupService
{
    /**
     * @var ConnectionInterface
     */
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Generate a database backup string
     *
     * * @param array $params
     * @return string
     */
    public function downloadBackup(array $params = []): string
    {
        $prefs = array(
            'tables'             => $params['tables'] ?? [],
            'ignore'             => $params['ignore'] ?? [],
            'filename'           => $params['filename'] ?? '',
            'format'             => $params['format'] ?? 'gzip', // gzip, zip, txt
            'add_drop'           => $params['add_drop'] ?? true,
            'add_insert'         => $params['add_insert'] ?? true,
            'newline'            => $params['newline'] ?? "\n",
            'foreign_key_checks' => $params['foreign_key_checks'] ?? true
        );

        if (count($prefs['tables']) === 0) {
            $prefs['tables'] = $this->db->listTables();
        }

        // Extract the prefs for simplicity
        extract($prefs);
        $output = '';

        // Do we need to include a statement to disable foreign key checks?
        if ($foreign_key_checks === false) {
            $output .= 'SET foreign_key_checks = 0;' . $newline;
        }

        foreach ((array)$tables as $table) {
            // Is the table in the "ignore" list?
            if (in_array($table, (array)$ignore, true)) {
                continue;
            }

            // Get the table schema
            $query = $this->db->query('SHOW CREATE TABLE ' . $this->db->escapeIdentifiers($this->db->database . '.' . $table));

            // No result means the table name was invalid
            if ($query === false) {
                continue;
            }

            // Write out the table schema
            $output .= '#' . $newline . '# TABLE STRUCTURE FOR: ' . $table . $newline . '#' . $newline . $newline;

            if ($add_drop === true) {
                $output .= 'DROP TABLE IF EXISTS ' . $this->db->protectIdentifiers($table) . ';' . $newline . $newline;
            }

            $i = 0;
            $result = $query->getResultArray();
            foreach ($result[0] as $val) {
                if ($i++ % 2) {
                    $output .= $val . ';' . $newline . $newline;
                }
            }

            // If inserts are not needed we're done...
            if ($add_insert === false) {
                continue;
            }

            // Grab all the data from the current table
            $query = $this->db->query('SELECT * FROM ' . $this->db->protectIdentifiers($table));

            if ($query->getFieldCount() === 0) {
                continue;
            }

            // Fetch the field names and determine if the field is an
            // integer type. We use this info to decide whether to
            // surround the data with quotes or not
            $i = 0;
            $field_str = '';
            $isInt = array();

            // Get field data using CI4's metadata to stay driver-agnostic
            $fields = $this->db->getFieldData($table);

            foreach ($fields as $field) {
                // Determine if the field is an integer type
                $isInt[$i] = in_array(strtolower($field->type), ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'], true);

                // Create a string of field names
                $field_str .= $this->db->escapeIdentifiers($field->name) . ', ';
                $i++;
            }

            // Trim off the end comma
            $field_str = preg_replace('/, $/', '', $field_str);

            // Build the insert string
            foreach ($query->getResultArray() as $row) {
                $valStr = '';
                $i = 0;
                foreach ($row as $v) {
                    if ($v === null) {
                        $valStr .= 'NULL';
                    } else {
                        // Escape the data if it's not an integer
                        $valStr .= ($isInt[$i] === false) ? $this->db->escape($v) : $v;
                    }
                    // Append a comma
                    $valStr .= ', ';
                    $i++;
                }
                // Remove the comma at the end of the string
                $valStr = preg_replace('/, $/', '', $valStr);

                // Build the INSERT string
                $output .= 'INSERT INTO ' . $this->db->protectIdentifiers($table) . ' (' . $field_str . ') VALUES (' . $valStr . ');' . $newline;
            }
            $output .= $newline . $newline;
        }

        // Do we need to include a statement to re-enable foreign key checks?
        if ($foreign_key_checks === false) {
            $output .= 'SET foreign_key_checks = 1;' . $newline;
        }

        return $output;
    }
}