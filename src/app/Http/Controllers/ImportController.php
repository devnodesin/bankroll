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
     * Import transactions from uploaded file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
            'bank_name' => 'required|string|max:100',
        ]);

        try {
            $file = $request->file('file');
            $bankName = $request->bank_name;

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

            // Validate column headers (case-insensitive)
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
                    'found' => array_values(array_filter($rows[0], fn($v) => !empty(trim($v))))
                ], 400);
            }

            // Get column indexes
            $dateIdx = array_search('date', $headers);
            $descIdx = array_search('description', $headers);
            $withdrawIdx = array_search('withdraw', $headers);
            $depositIdx = array_search('deposit', $headers);
            $balanceIdx = array_search('balance', $headers);

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
                    $date = $this->parseDate($row[$dateIdx]);
                    
                    if (!$date) {
                        $errors[] = "Row " . ($i + 1) . ": Invalid date format";
                        continue;
                    }

                    $withdraw = $this->parseAmount($row[$withdrawIdx] ?? '');
                    $deposit = $this->parseAmount($row[$depositIdx] ?? '');
                    $balance = $this->parseAmount($row[$balanceIdx] ?? '');

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
     * Parse date from various formats
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Try to parse as Excel date number
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return \Carbon\Carbon::instance($date);
            } catch (\Exception $e) {
                // Continue to try other formats
            }
        }

        // Try common date formats
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                $date = \Carbon\Carbon::createFromFormat($format, trim($value));
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try natural language parsing
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
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
