<?php

namespace App\Services\Parsers;

/**
 * Standard transaction parser
 * 
 * Handles the standard transaction format with separate Withdraw and Deposit columns:
 * - Date
 * - Description
 * - Withdraw (debit amount)
 * - Deposit (credit amount)
 * - Balance
 */
class StandardTransactionParser extends BaseTransactionParser
{
    public function getIdentifier(): string
    {
        return 'standard';
    }

    public function getName(): string
    {
        return 'Standard Format';
    }

    public function getDescription(): string
    {
        return 'Files with separate Withdraw and Deposit columns (standard format)';
    }

    public function getRequiredFields(): array
    {
        return ['date', 'description', 'balance'];
    }

    /**
     * Auto-detect column mappings for standard format
     * 
     * @param array $headers Column headers from the file
     * @return array Column mappings
     */
    public function autoDetectColumns(array $headers): array
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
            foreach ($patterns as $field => $fieldPatterns) {
                if ($this->matchesPattern($header, $fieldPatterns)) {
                    if ($mappings[$field] === null) {
                        $mappings[$field] = $index;
                        break;
                    }
                }
            }
        }

        return $mappings;
    }

    /**
     * Parse a single row of transaction data in standard format
     * 
     * @param array $row The row data
     * @param array $columnMappings Column index mappings
     * @param string $dateFormat The date format to use for parsing
     * @param string $bankName The bank name for this transaction
     * @return array|null Parsed transaction data or null if invalid
     * @throws \Exception If row parsing fails with specific error
     */
    public function parseRow(array $row, array $columnMappings, string $dateFormat, string $bankName): ?array
    {
        // Parse date
        $date = $this->parseDate($row[$columnMappings['date']], $dateFormat);
        
        if (!$date) {
            $formatDisplay = $this->getDateFormatDisplay($dateFormat);
            throw new \Exception("The date '{$row[$columnMappings['date']]}' is not in the expected format. Expected {$formatDisplay}");
        }

        // Parse amounts
        $withdraw = $this->parseAmount($row[$columnMappings['withdraw']] ?? '');
        $deposit = $this->parseAmount($row[$columnMappings['deposit']] ?? '');
        $balance = $this->parseAmount($row[$columnMappings['balance']] ?? '');

        // Validate required fields
        if ($balance === null) {
            throw new \Exception("Balance field is required and must contain a valid number");
        }

        if ($withdraw === null && $deposit === null) {
            throw new \Exception("Either Withdraw or Deposit must have a value (both cannot be empty)");
        }

        // Return standardized transaction data
        return [
            'bank_name' => $bankName,
            'date' => $date,
            'description' => trim($row[$columnMappings['description']] ?? ''),
            'withdraw' => $withdraw,
            'deposit' => $deposit,
            'balance' => $balance,
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('m'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
