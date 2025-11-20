<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Parsers\TransactionParserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    /**
     * @var TransactionParserFactory
     */
    private TransactionParserFactory $parserFactory;

    /**
     * Constructor
     */
    public function __construct(TransactionParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
    }

    /**
     * Preview file and suggest column mappings
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ], [
            'file.required' => 'Please select a file to preview.',
            'file.mimes' => 'File must be in Excel (.xlsx, .xls) or CSV format.',
            'file.max' => 'File size cannot exceed 5MB.',
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

            // Auto-detect best parser and column mappings
            $parser = $this->parserFactory->autoDetectParser($headers);
            $mappings = $parser->autoDetectColumns($headers);

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'preview' => $previewRows,
                'mappings' => $mappings,
                'parser_type' => $parser->getIdentifier(),
                'parser_name' => $parser->getName(),
                'available_parsers' => $this->parserFactory->getParserOptions(),
            ]);
        } catch (\Exception $e) {
            Log::error('Preview error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to read the file. Please ensure it is a valid Excel or CSV file and try again.'
            ], 500);
        }
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
            'parser_type' => 'nullable|string|in:standard,credit-debit',
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'File must be in Excel (.xlsx, .xls) or CSV format.',
            'file.max' => 'File size cannot exceed 5MB.',
            'bank_name.required' => 'Please select a bank.',
            'bank_name.max' => 'Bank name cannot exceed 100 characters.',
            'date_format.required' => 'Please select a date format.',
            'date_format.in' => 'Invalid date format selected.',
            'parser_type.in' => 'Invalid parser type selected.',
        ]);

        try {
            $file = $request->file('file');
            $bankName = $request->bank_name;
            $columnMappings = $request->column_mappings ? json_decode($request->column_mappings, true) : null;
            $dateFormat = $request->date_format;
            $parserType = $request->parser_type ?? 'standard';
            
            // Get the appropriate parser
            $parser = $this->parserFactory->getParser($parserType);
            
            // Ensure bank exists in banks table
            \App\Models\Bank::firstOrCreate(['name' => $bankName]);

            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded file is empty. Please check your file and try again.'
                ], 400);
            }

            // Get column mappings
            if ($columnMappings) {
                // Use provided column mappings - already validated by frontend
            } else {
                // Try exact column name matching for backward compatibility
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
                        'message' => 'Your file is missing required columns: ' . implode(', ', $missingColumns) . '. Please use the column mapping feature to map your columns correctly.',
                        'required' => $requiredColumns,
                        'found' => array_values(array_filter($rows[0], fn($v) => !empty(trim($v)))),
                        'needs_mapping' => true,
                    ], 400);
                }

                // Build column mappings from exact match (for standard parser only)
                $columnMappings = [
                    'date' => array_search('date', $headers),
                    'description' => array_search('description', $headers),
                    'withdraw' => array_search('withdraw', $headers),
                    'deposit' => array_search('deposit', $headers),
                    'balance' => array_search('balance', $headers),
                ];
            }

            // Validate that all required columns are mapped for the selected parser
            $missingMappings = $parser->validateMappings($columnMappings);
            if (!empty($missingMappings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please map the following required columns: ' . implode(', ', $missingMappings) . '. These fields are mandatory for importing transactions.',
                ], 400);
            }

            $transactions = [];
            $errors = [];

            // Process data rows using the parser
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip empty rows
                if (empty(array_filter($row, fn($v) => !empty(trim($v))))) {
                    continue;
                }

                try {
                    $transaction = $parser->parseRow($row, $columnMappings, $dateFormat, $bankName);
                    if ($transaction) {
                        $transactions[] = $transaction;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                Log::error('Import validation errors', ['errors' => $errors]);
                return response()->json([
                    'success' => false,
                    'message' => 'Found ' . count($errors) . ' error(s) in your file. Please fix the issues below and try again.',
                    'errors' => $errors
                ], 400);
            }

            if (empty($transactions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid transactions found in the file. Please check that your file contains transaction data and try again.'
                ], 400);
            }

            // Insert transactions in a database transaction
            DB::beginTransaction();
            try {
                Transaction::insert($transactions);
                DB::commit();

                $count = count($transactions);
                return response()->json([
                    'success' => true,
                    'message' => "Successfully imported {$count} transaction" . ($count === 1 ? '' : 's') . " for {$bankName}.",
                    'count' => $count
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
                'message' => 'Import failed due to an unexpected error. Please verify your file format and try again.'
            ], 500);
        }
    }
}
