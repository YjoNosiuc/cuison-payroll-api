<?php

declare(strict_types=1);

namespace App;

use RuntimeException;
use UnexpectedValueException;

class CsvRepository
{
    private static array $cache = [];

    public function employees(): array
    {
        return $this->read('employees.csv');
    }

    public function payroll(): array
    {
        return $this->read('payroll.csv');
    }

    private function read(string $filename): array
    {
        $path = __DIR__ . '/../data/' . $filename;

        if (array_key_exists($path, self::$cache)) {
            return self::$cache[$path];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open CSV file: ' . $filename);
        }

        try {
            $headers = fgetcsv($handle, null, ',', '"', '');
            if ($headers === false) {
                throw new UnexpectedValueException('CSV file is empty: ' . $filename);
            }

            $rows = [];
            while (($values = fgetcsv($handle, null, ',', '"', '')) !== false) {
                if (count($values) !== count($headers)) {
                    throw new UnexpectedValueException('Malformed CSV row in ' . $filename);
                }

                $row = array_combine($headers, $values);
                if ($row === false) {
                    throw new UnexpectedValueException('Malformed CSV row in ' . $filename);
                }

                $rows[] = $this->castRow($row, $filename);
            }
        } finally {
            fclose($handle);
        }

        self::$cache[$path] = $rows;

        return $rows;
    }

    private function castRow(array $row, string $filename): array
    {
        foreach ($row as $column => $value) {
            if ($value === null || trim($value) === '' || strtolower(trim($value)) === 'null') {
                $row[$column] = null;
                continue;
            }

            if ($filename === 'employees.csv') {
                if (in_array($column, ['id', 'position_id', 'department_id', 'manager_id'], true)) {
                    $row[$column] = (int) $value;
                }
            } else {
                if (in_array($column, ['id', 'employee_id'], true)) {
                    $row[$column] = (int) $value;
                } elseif (in_array($column, ['gross_pay', 'net_pay'], true)) {
                    $row[$column] = (float) $value;
                }
            }
        }

        return $row;
    }
}