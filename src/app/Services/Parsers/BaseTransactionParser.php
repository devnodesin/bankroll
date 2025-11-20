<?php

namespace App\Services\Parsers;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Base abstract class for transaction parsers
 * 
 * Provides common functionality for all parsers such as date parsing and amount parsing.
 */
abstract class BaseTransactionParser implements TransactionParserInterface
{
    /**
     * Parse date using the specified format
     * 
     * @param mixed $value The date value from the file
     * @param string $format The expected date format
     * @return Carbon|null Parsed date or null if invalid
     */
    protected function parseDate($value, string $format): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        // Try to parse as Excel date number first
        if (is_numeric($value)) {
            try {
                $date = Date::excelToDateTimeObject($value);
                return Carbon::instance($date);
            } catch (\Exception $e) {
                // Continue to try the specified format
            }
        }

        // Try to parse with the specified format
        try {
            $date = Carbon::createFromFormat($format, trim($value));
            if ($date && $date->format($format) === trim($value)) {
                // Validate that the parsed date matches the input exactly
                return $date;
            }
        } catch (\Exception $e) {
            // Format parsing failed
        }

        // Try variant formats (replace separators)
        $separators = ['/', '-', '.'];
        $currentSeparator = '/';
        if (strpos($format, '-') !== false) {
            $currentSeparator = '-';
        } elseif (strpos($format, '.') !== false) {
            $currentSeparator = '.';
        }

        foreach ($separators as $separator) {
            if ($separator === $currentSeparator) {
                continue;
            }
            
            $variantFormat = str_replace($currentSeparator, $separator, $format);
            try {
                $date = Carbon::createFromFormat($variantFormat, trim($value));
                if ($date && $date->format($variantFormat) === trim($value)) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Parse amount from string
     * 
     * @param mixed $value The amount value from the file
     * @return float|null Parsed amount or null if empty/invalid
     */
    protected function parseAmount($value): ?float
    {
        if (empty($value) || trim($value) === '' || trim($value) === '-') {
            return null;
        }

        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        if ($cleaned === '' || $cleaned === '-') {
            return null;
        }

        return (float) $cleaned;
    }

    /**
     * Get display format for date format code
     * 
     * @param string $format The date format code
     * @return string Human-readable format description
     */
    protected function getDateFormatDisplay(string $format): string
    {
        $displays = [
            'd/m/Y' => 'DD/MM/YYYY (e.g., 15/03/2024)',
            'd/m/y' => 'DD/MM/YY (e.g., 15/03/24)',
            'm/d/Y' => 'MM/DD/YYYY (e.g., 03/15/2024)',
            'Y-m-d' => 'YYYY-MM-DD (e.g., 2024-03-15)',
        ];

        return $displays[$format] ?? $format;
    }

    /**
     * Validate that all required columns are mapped
     * 
     * @param array $columnMappings Column mappings to validate
     * @return array Array of missing required field names
     */
    public function validateMappings(array $columnMappings): array
    {
        $missing = [];
        foreach ($this->getRequiredFields() as $field) {
            if (!isset($columnMappings[$field]) || $columnMappings[$field] === null) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    /**
     * Check if a header matches any of the given patterns
     * 
     * @param string $header The header to check
     * @param array $patterns Array of patterns to match against
     * @return bool True if header matches any pattern
     */
    protected function matchesPattern(string $header, array $patterns): bool
    {
        $headerLower = strtolower(trim($header));
        
        foreach ($patterns as $pattern) {
            if ($headerLower === $pattern || strpos($headerLower, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
