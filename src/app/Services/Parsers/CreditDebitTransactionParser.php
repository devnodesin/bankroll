<?php

namespace App\Services\Parsers;

/**
 * Credit/Debit transaction parser
 * 
 * Handles transaction formats with a single Amount column and a CR/DR indicator:
 * - Date
 * - Description
 * - Amount (single column for both credit and debit)
 * - Type/CR-DR (indicator: CR/Credit/C for credit, DR/Debit/D for debit)
 * - Balance
 * 
 * This parser maps:
 * - DR/Debit amounts to Withdraw field
 * - CR/Credit amounts to Deposit field
 */
class CreditDebitTransactionParser extends BaseTransactionParser
{
    /**
     * Supported credit transaction indicators
     */
    public const CREDIT_INDICATORS = ['CR', 'CREDIT', 'C', 'CREDITED'];
    
    /**
     * Supported debit transaction indicators
     */
    public const DEBIT_INDICATORS = ['DR', 'DEBIT', 'D', 'DEBITED'];
    
    public function getIdentifier(): string
    {
        return 'credit-debit';
    }

    public function getName(): string
    {
        return 'Credit/Debit Format';
    }

    public function getDescription(): string
    {
        return 'Files with single Amount column and CR/DR indicator';
    }

    public function getRequiredFields(): array
    {
        return ['date', 'description', 'amount', 'type', 'balance'];
    }

    /**
     * Auto-detect column mappings for CR/DR format
     * 
     * @param array $headers Column headers from the file
     * @return array Column mappings
     */
    public function autoDetectColumns(array $headers): array
    {
        $mappings = [
            'date' => null,
            'description' => null,
            'amount' => null,
            'type' => null,
            'balance' => null,
        ];

        $patterns = [
            'date' => ['date', 'transaction date', 'txn date', 'posting date', 'trans date', 'value date'],
            'description' => ['description', 'desc', 'particulars', 'details', 'narration', 'remarks', 'transaction details'],
            'amount' => ['amount', 'transaction amount', 'txn amount', 'value'],
            'type' => ['type', 'cr/dr', 'crdr', 'cr-dr', 'credit/debit', 'transaction type', 'txn type'],
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
     * Parse a single row of transaction data in CR/DR format
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

        // Parse amount
        $amount = $this->parseAmount($row[$columnMappings['amount']] ?? '');
        $balance = $this->parseAmount($row[$columnMappings['balance']] ?? '');

        // Validate required fields
        if ($balance === null) {
            throw new \Exception("Balance field is required and must contain a valid number");
        }

        if ($amount === null) {
            throw new \Exception("Amount field is required and must contain a valid number");
        }

        // Parse transaction type (CR/DR indicator)
        $type = strtoupper(trim($row[$columnMappings['type']] ?? ''));
        
        // Determine if it's credit or debit
        $isCredit = $this->isCredit($type);
        $isDebit = $this->isDebit($type);

        if (!$isCredit && !$isDebit) {
            throw new \Exception("Type field must be CR/Credit (for deposit) or DR/Debit (for withdrawal). Found: '{$type}'");
        }

        // Map to withdraw/deposit based on type
        $withdraw = $isDebit ? $amount : null;
        $deposit = $isCredit ? $amount : null;

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

    /**
     * Check if type indicator represents a credit transaction
     * 
     * @param string $type The type indicator
     * @return bool True if credit
     */
    private function isCredit(string $type): bool
    {
        return in_array($type, self::CREDIT_INDICATORS);
    }

    /**
     * Check if type indicator represents a debit transaction
     * 
     * @param string $type The type indicator
     * @return bool True if debit
     */
    private function isDebit(string $type): bool
    {
        return in_array($type, self::DEBIT_INDICATORS);
    }

    /**
     * Get field configuration for UI rendering
     * 
     * @return array Array of field configurations
     */
    public function getFieldConfiguration(): array
    {
        return [
            ['key' => 'date', 'label' => 'Date', 'required' => true, 'col' => 'col-md-6'],
            ['key' => 'description', 'label' => 'Description', 'required' => true, 'col' => 'col-md-6'],
            ['key' => 'amount', 'label' => 'Amount', 'required' => true, 'col' => 'col-md-4'],
            ['key' => 'type', 'label' => 'Type (CR/DR)', 'required' => true, 'col' => 'col-md-4'],
            ['key' => 'balance', 'label' => 'Balance', 'required' => true, 'col' => 'col-md-4'],
        ];
    }
}
