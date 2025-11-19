<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    /**
     * Preview file and suggest column mappings
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('file');
            
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file is empty.'
                ], 400);
            }

            // Get headers and preview rows
            $headers = array_map('trim', $rows[0]);
            $previewRows = array_slice($rows, 1, 3); // Get first 3 data rows

            // Auto-detect column mappings
            $mappings = $this->autoDetectColumns($headers);

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'preview' => $previewRows,
                'mappings' => $mappings,
            ]);
        } catch (\Exception $e) {
            Log::error('Preview error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-detect column mappings based on header names
     */
    private function autoDetectColumns($headers)
    {
        $mappings = [
            'date' => null,
            'description' => null,
            'withdraw' => null,
            'deposit' => null,
            'balance' => null,
        ];

        $patterns = [
            'date' => ['date', 'transaction date', 'txn date', 'posting date', 'trans date', 'value date'],
            'description' => ['description', 'desc', 'particulars', 'details', 'narration', 'remarks', 'transaction details'],
            'withdraw' => ['withdraw', 'withdrawal', 'debit', 'dr', 'amount debited', 'debit amount', 'paid out'],
            'deposit' => ['deposit', 'credit', 'cr', 'amount credited', 'credit amount', 'paid in'],
            'balance' => ['balance', 'closing balance', 'available balance', 'running balance', 'current balance'],
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($patterns as $field => $fieldPatterns) {
                foreach ($fieldPatterns as $pattern) {
                    if ($headerLower === $pattern || strpos($headerLower, $pattern) !== false) {
                        if ($mappings[$field] === null) {
                            $mappings[$field] = $index;
                            break 2;
                        }
                    }
                }
            }
        }

        return $mappings;
    }

    /**
     * Import transactions from uploaded file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
            'bank_name' => 'required|string|max:100',
            'column_mappings' => 'nullable|json',
            'date_format' => 'required|string|in:d/m/Y,d/m/y,m/d/Y,Y-m-d',
        ]);

        try {
            $file = $request->file('file');
            $bankName = $request->bank_name;
            $columnMappings = $request->column_mappings ? json_decode($request->column_mappings, true) : null;
            $dateFormat = $request->date_format;
            
            // Ensure bank exists in banks table
            \App\Models\Bank::firstOrCreate(['name' => $bankName]);

            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file is empty.'
                ], 400);
            }

            // Get column indexes - either from mappings or by exact match
            if ($columnMappings) {
                // Use provided column mappings
                $dateIdx = $columnMappings['date'] ?? null;
                $descIdx = $columnMappings['description'] ?? null;
                $withdrawIdx = $columnMappings['withdraw'] ?? null;
                $depositIdx = $columnMappings['deposit'] ?? null;
                $balanceIdx = $columnMappings['balance'] ?? null;

                // Validate that all required columns are mapped
                $missingMappings = [];
                if ($dateIdx === null) $missingMappings[] = 'date';
                if ($descIdx === null) $missingMappings[] = 'description';
                if ($balanceIdx === null) $missingMappings[] = 'balance';

                if (!empty($missingMappings)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Missing required column mappings: ' . implode(', ', $missingMappings),
                    ], 400);
                }
            } else {
                // Try exact column name matching (case-insensitive) - backward compatibility
                $headers = array_map('trim', array_map('strtolower', $rows[0]));
                $requiredColumns = ['date', 'description', 'withdraw', 'deposit', 'balance'];
                
                $missingColumns = [];
                foreach ($requiredColumns as $column) {
                    if (!in_array($column, $headers)) {
                        $missingColumns[] = $column;
                    }
                }

                if (!empty($missingColumns)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Missing required columns: ' . implode(', ', $missingColumns),
                        'required' => $requiredColumns,
                        'found' => array_values(array_filter($rows[0], fn($v) => !empty(trim($v)))),
                        'needs_mapping' => true, // Signal that column mapping is needed
                    ], 400);
                }

                // Get column indexes from exact match
                $dateIdx = array_search('date', $headers);
                $descIdx = array_search('description', $headers);
                $withdrawIdx = array_search('withdraw', $headers);
                $depositIdx = array_search('deposit', $headers);
                $balanceIdx = array_search('balance', $headers);
            }

            $transactions = [];
            $errors = [];

            // Process data rows
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip empty rows
                if (empty(array_filter($row, fn($v) => !empty(trim($v))))) {
                    continue;
                }

                try {
                    $date = $this->parseDate($row[$dateIdx], $dateFormat);
                    
                    if (!$date) {
                        $formatDisplay = $this->getDateFormatDisplay($dateFormat);
                        $errors[] = "Row " . ($i + 1) . ": Invalid date format '{$row[$dateIdx]}'. Expected {$formatDisplay}";
                        continue;
                    }

                    $withdraw = $this->parseAmount($row[$withdrawIdx] ?? '');
                    $deposit = $this->parseAmount($row[$depositIdx] ?? '');
                    $balance = $this->parseAmount($row[$balanceIdx] ?? '');

                    if ($balance === null) {
                        $errors[] = "Row " . ($i + 1) . ": Balance is required";
                        continue;
                    }

                    if ($withdraw === null && $deposit === null) {
                        $errors[] = "Row " . ($i + 1) . ": At least one of Withdraw or Deposit must have a value";
                        continue;
                    }

                    $transactions[] = [
                        'bank_name' => $bankName,
                        'date' => $date,
                        'description' => trim($row[$descIdx] ?? ''),
                        'withdraw' => $withdraw,
                        'deposit' => $deposit,
                        'balance' => $balance,
                        'year' => (int) $date->format('Y'),
                        'month' => (int) $date->format('m'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                Log::error('Import validation errors', ['errors' => $errors]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors found',
                    'errors' => $errors
                ], 400);
            }

            if (empty($transactions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid transactions found in the file.'
                ], 400);
            }

            // Insert transactions in a database transaction
            DB::beginTransaction();
            try {
                Transaction::insert($transactions);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => count($transactions) . ' transactions imported successfully.',
                    'count' => count($transactions)
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Import database error', ['error' => $e->getMessage()]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Import error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse date using the specified format
     */
    private function parseDate($value, $format)
    {
        if (empty($value)) {
            return null;
        }

        // Try to parse as Excel date number first
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return \Carbon\Carbon::instance($date);
            } catch (\Exception $e) {
                // Continue to try the specified format
            }
        }

        // Try to parse with the specified format
        try {
            $date = \Carbon\Carbon::createFromFormat($format, trim($value));
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
                $date = \Carbon\Carbon::createFromFormat($variantFormat, trim($value));
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
     * Get display format for date format code
     */
    private function getDateFormatDisplay($format)
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
     * Parse amount from string
     */
    private function parseAmount($value)
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
}
