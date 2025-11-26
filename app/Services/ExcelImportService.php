<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ExcelImportService
{
    /**
     * Import messages from an Excel file into a MessageCollection.
     *
     * @param  mixed       $collection   Eloquent model (MessageCollection)
     * @param  string      $fullPath     Full absolute path to Excel file
     * @return int                       Number of imported rows
     *
     * @throws \Exception
     */
    public static function importMessagesFromExcel($collection, string $fullPath): int
    {
        if (!$fullPath) {
            throw new \Exception("Fayl yo'li ko'rsatilmagan");
        }

        if (!file_exists($fullPath)) {
            throw new \Exception("Excel fayl topilmadi: {$fullPath}");
        }

        // Read excel into arrays
        try {
            $sheets = Excel::toArray(null, $fullPath);
        } catch (\Throwable $e) {
            throw new \Exception("Excel faylini o'qishda xatolik: " . $e->getMessage());
        }

        if (empty($sheets) || empty($sheets[0])) {
            throw new \Exception("Excel fayl bo'sh yoki noto'g'ri formatda");
        }

        $sheet = $sheets[0];
        $hasHeadings = true;

        if ($hasHeadings) {
            $headings = array_map(fn($h) => trim((string)$h), $sheet[0]);
            $rows = array_slice($sheet, 1);
        } else {
            $headings = null;
            $rows = $sheet;
        }

        $imported = 0;

        DB::transaction(function() use ($rows, $headings, $collection, &$imported) {
            $possiblePhoneKeys = [
                'Telefon raqami', 
                'Telefon', 
                'phone', 
                'telefon raqami', 
                'telefon', 
                'Phone',
                'TELEFON RAQAMI',
                'TELEFON'
            ];

            foreach ($rows as $row) {
                // Skip empty rows
                if (self::isEmptyRow($row)) {
                    continue;
                }

                $rowAssoc = self::buildRowAssoc($row, $headings);
                $phone = self::extractPhone($rowAssoc, $possiblePhoneKeys);

                if (!$phone) {
                    continue;
                }

                $normalizedPhone = self::normalizePhone($phone);

                if (!$normalizedPhone) {
                    continue;
                }

                $messageData = self::buildMessageData($rowAssoc, $collection->template);

                $payload = [
                    'phone' => $normalizedPhone,
                    'details' => $messageData,
                ];

                if (isset($messageData['text'])) {
                    $payload['text'] = $messageData['text'];
                }

                $collection->messages()->create($payload);
                $imported++;
            }
        });

        return $imported;
    }

    /**
     * Resolve file path (handle both absolute and relative paths)
     */
    private static function resolveFilePath(string $filePath): string
    {
        // Check if it's an absolute path
        $isAbsolute = PHP_OS_FAMILY === 'Windows'
            ? preg_match('/^[A-Za-z]:[\\\\\/]/', $filePath)
            : str_starts_with($filePath, '/');

        if ($isAbsolute && file_exists($filePath)) {
            return $filePath;
        }

        // Try storage_path
        $storagePath = storage_path('app/' . ltrim($filePath, '/'));
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        // Try public path
        $publicPath = public_path($filePath);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        // Try base path
        $basePath = base_path($filePath);
        if (file_exists($basePath)) {
            return $basePath;
        }

        // Return storage path as default (will show proper error)
        return $storagePath;
    }

    /**
     * Check if row is empty
     */
    private static function isEmptyRow(array $row): bool
    {
        return count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0;
    }

    /**
     * Build associative array from row
     */
    private static function buildRowAssoc(array $row, ?array $headings): array
    {
        $rowAssoc = [];

        if (is_array($headings)) {
            foreach ($headings as $i => $heading) {
                $rowAssoc[$heading] = $row[$i] ?? null;
            }
        } else {
            foreach ($row as $i => $val) {
                $rowAssoc[$i] = $val;
            }
        }

        return $rowAssoc;
    }

    /**
     * Extract phone number from row
     */
    private static function extractPhone(array $rowAssoc, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($rowAssoc[$key]) && trim((string)$rowAssoc[$key]) !== '') {
                return (string)$rowAssoc[$key];
            }
        }

        // Fallback to first column
        if (isset($rowAssoc[0]) && trim((string)$rowAssoc[0]) !== '') {
            return (string)$rowAssoc[0];
        }

        return null;
    }

    /**
     * Normalize phone number to Uzbekistan format
     */
    private static function normalizePhone(string $phone): ?string
    {
        // Remove all non-digit characters
        $normalized = preg_replace('/\D+/', '', $phone);

        if (!$normalized) {
            return null;
        }

        // Handle different formats
        if (strlen($normalized) === 9) {
            // 9 digits: add 998 prefix
            return '998' . $normalized;
        } elseif (strlen($normalized) === 10 && str_starts_with($normalized, '0')) {
            // 10 digits starting with 0: replace 0 with 998
            return '998' . substr($normalized, 1);
        } elseif (strlen($normalized) === 12 && str_starts_with($normalized, '998')) {
            // Already in correct format
            return $normalized;
        } elseif (strlen($normalized) === 13 && str_starts_with($normalized, '+998')) {
            // Has + prefix
            return substr($normalized, 1);
        }

        // Return as is if valid length
        if (strlen($normalized) >= 9 && strlen($normalized) <= 15) {
            return $normalized;
        }

        return null;
    }

    /**
     * Build message data with template variables
     */
    private static function buildMessageData(array $rowAssoc, $template): array
    {
        if (!$template || empty($template->variables)) {
            return $rowAssoc;
        }

        $messageData = [];

        foreach ($template->variables as $var) {
            $label = is_array($var) 
                ? ($var['value'] ?? $var['key'] ?? null) 
                : ($var->value ?? $var->key ?? null);
            
            $key = is_array($var) 
                ? ($var['key'] ?? $label) 
                : ($var->key ?? $label);

            $messageData[$key] = $rowAssoc[$label] ?? null;
        }

        return $messageData;
    }
}