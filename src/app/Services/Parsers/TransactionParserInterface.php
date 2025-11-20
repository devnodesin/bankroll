<?php

namespace App\Services\Parsers;

/**
 * Interface for transaction parsers
 * 
 * This interface defines the contract that all transaction parsers must implement.
 * Different parsers can handle different transaction formats (standard, CR/DR, etc.)
 * while outputting the same standardized transaction data structure.
 */
interface TransactionParserInterface
{
    /**
     * Get the unique identifier for this parser
     * 
     * @return string Parser identifier (e.g., 'standard', 'credit-debit')
     */
    public function getIdentifier(): string;

    /**
     * Get the human-readable name of this parser
     * 
     * @return string Parser display name
     */
    public function getName(): string;

    /**
     * Get the description of what this parser handles
     * 
     * @return string Parser description
     */
    public function getDescription(): string;

    /**
     * Auto-detect column mappings from headers
     * 
     * @param array $headers Column headers from the file
     * @return array Column mappings (e.g., ['date' => 0, 'description' => 1, ...])
     */
    public function autoDetectColumns(array $headers): array;

    /**
     * Get the required field names for this parser
     * 
     * @return array List of required field names
     */
    public function getRequiredFields(): array;

    /**
     * Parse a single row of transaction data
     * 
     * @param array $row The row data
     * @param array $columnMappings Column index mappings
     * @param string $dateFormat The date format to use for parsing
     * @param string $bankName The bank name for this transaction
     * @return array|null Parsed transaction data or null if invalid
     * @throws \Exception If row parsing fails with specific error
     */
    public function parseRow(array $row, array $columnMappings, string $dateFormat, string $bankName): ?array;

    /**
     * Validate that all required columns are mapped
     * 
     * @param array $columnMappings Column mappings to validate
     * @return array Array of missing required field names (empty if all present)
     */
    public function validateMappings(array $columnMappings): array;

    /**
     * Get field configuration for UI rendering
     * 
     * Returns an array of field configurations with metadata for each field:
     * - key: Field identifier (e.g., 'date', 'description')
     * - label: Human-readable label for UI
     * - required: Whether the field is required (boolean)
     * - col: Bootstrap column class (e.g., 'col-md-6')
     * 
     * @return array Array of field configurations
     */
    public function getFieldConfiguration(): array;
}
