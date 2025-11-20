# Transaction Parser System

## Overview

The Transaction Parser System provides a modular, extensible architecture for importing transaction data from various file formats. The system uses the Strategy pattern to support different transaction layouts while maintaining a consistent output format compatible with the existing database schema.

**Key Benefit:** No database changes required - all parsers output the same standardized transaction structure.

## Architecture

### Components

1. **TransactionParserInterface** - Contract that all parsers must implement
2. **BaseTransactionParser** - Abstract base class with shared functionality
3. **StandardTransactionParser** - Handles standard Withdraw/Deposit format
4. **CreditDebitTransactionParser** - Handles single Amount + CR/DR indicator format
5. **TransactionParserFactory** - Manages parser instances and provides auto-detection

### Class Diagram

```
TransactionParserInterface
        ↑
        |
BaseTransactionParser (abstract)
        ↑
        |
        ├── StandardTransactionParser
        └── CreditDebitTransactionParser

TransactionParserFactory
        → uses TransactionParserInterface
```

## Available Parsers

### 1. Standard Transaction Parser

**Identifier:** `standard`

**Format:** Separate columns for debits and credits

**Required Columns:**
- Date
- Description
- Balance

**Optional Columns:**
- Withdraw (debit amount)
- Deposit (credit amount)

**Example File:**
```csv
Date,Description,Withdraw,Deposit,Balance
15/03/2024,ATM Withdrawal,500.00,,4500.00
16/03/2024,Salary Credit,,5000.00,9500.00
```

**Column Variations Detected:**
- Date: "Date", "Transaction Date", "Txn Date", "Posting Date", etc.
- Description: "Description", "Particulars", "Details", "Narration", etc.
- Withdraw: "Withdraw", "Debit", "DR", "Amount Debited", etc.
- Deposit: "Deposit", "Credit", "CR", "Amount Credited", etc.
- Balance: "Balance", "Closing Balance", "Running Balance", etc.

### 2. Credit/Debit Transaction Parser

**Identifier:** `credit-debit`

**Format:** Single amount column with CR/DR indicator

**Required Columns:**
- Date
- Description
- Amount
- Type (CR/DR indicator)
- Balance

**Example File:**
```csv
Date,Description,Amount,Type,Balance
15/03/2024,ATM Withdrawal,500.00,DR,4500.00
16/03/2024,Salary Credit,5000.00,CR,9500.00
```

**Type Indicators:**
- Credit: `CR`, `CREDIT`, `C`, `CREDITED`
- Debit: `DR`, `DEBIT`, `D`, `DEBITED`

**Column Variations Detected:**
- Type: "Type", "CR/DR", "CRDR", "CR-DR", "Credit/Debit", "Transaction Type", etc.

**Mapping:**
- DR transactions → `withdraw` field in database
- CR transactions → `deposit` field in database

## How It Works

### Import Flow

1. **File Upload** → User uploads transaction file
2. **Preview** → System analyzes headers and auto-detects best parser
3. **Parser Selection** → User can override auto-detection if needed
4. **Column Mapping** → System suggests column mappings, user confirms
5. **Parsing** → Selected parser processes each row
6. **Validation** → Data is validated according to parser rules
7. **Storage** → Standardized transactions saved to database

### Auto-Detection

The factory analyzes file headers and selects the parser with:
- Most successfully mapped columns
- All required fields detected
- Best pattern match score

If no clear match, defaults to `StandardTransactionParser`.

## Creating a New Parser

### Step 1: Implement the Interface

Create a new parser class that implements `TransactionParserInterface`:

```php
<?php

namespace App\Services\Parsers;

class MyCustomParser extends BaseTransactionParser
{
    public function getIdentifier(): string
    {
        return 'my-custom-format';
    }

    public function getName(): string
    {
        return 'My Custom Format';
    }

    public function getDescription(): string
    {
        return 'Description of what this parser handles';
    }

    public function getRequiredFields(): array
    {
        return ['date', 'description', 'balance', 'custom_field'];
    }

    public function autoDetectColumns(array $headers): array
    {
        $mappings = [
            'date' => null,
            'description' => null,
            'balance' => null,
            'custom_field' => null,
        ];

        $patterns = [
            'date' => ['date', 'transaction date'],
            'description' => ['description', 'details'],
            'balance' => ['balance', 'closing balance'],
            'custom_field' => ['custom', 'special field'],
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

    public function parseRow(array $row, array $columnMappings, string $dateFormat, string $bankName): ?array
    {
        // Parse date using inherited method
        $date = $this->parseDate($row[$columnMappings['date']], $dateFormat);
        
        if (!$date) {
            $formatDisplay = $this->getDateFormatDisplay($dateFormat);
            throw new \Exception("Invalid date format. Expected {$formatDisplay}");
        }

        // Parse your custom fields
        $customValue = $row[$columnMappings['custom_field']];
        
        // Convert to standardized format
        $withdraw = null;
        $deposit = null;
        
        // Your custom logic to determine withdraw/deposit
        // ...

        // Return standardized transaction data
        return [
            'bank_name' => $bankName,
            'date' => $date,
            'description' => trim($row[$columnMappings['description']] ?? ''),
            'withdraw' => $withdraw,
            'deposit' => $deposit,
            'balance' => $this->parseAmount($row[$columnMappings['balance']]),
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('m'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
```

### Step 2: Register the Parser

Add your parser to the factory in `TransactionParserFactory.php`:

```php
public function __construct()
{
    $this->registerParser(new StandardTransactionParser());
    $this->registerParser(new CreditDebitTransactionParser());
    $this->registerParser(new MyCustomParser()); // Add your parser
}
```

### Step 3: Update Validation

If needed, update the validation in `ImportController.php`:

```php
'parser_type' => 'nullable|string|in:standard,credit-debit,my-custom-format',
```

### Step 4: Add Tests

Create comprehensive tests for your parser:

```php
// tests/Unit/Parsers/MyCustomParserTest.php
public function test_parse_row_correctly()
{
    $parser = new MyCustomParser();
    $row = ['15/03/2024', 'Test', '100.00', 'CUSTOM_VALUE', '900.00'];
    $mappings = [
        'date' => 0,
        'description' => 1,
        'custom_field' => 3,
        'balance' => 4,
    ];
    
    $result = $parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    
    $this->assertNotNull($result);
    $this->assertEquals('Test Bank', $result['bank_name']);
}
```

## Utility Methods (from BaseTransactionParser)

### Date Parsing

```php
protected function parseDate($value, string $format): ?Carbon
```

Handles:
- Excel serial date numbers
- Multiple date separators (/, -, .)
- Various date formats (d/m/Y, m/d/Y, Y-m-d, etc.)

### Amount Parsing

```php
protected function parseAmount($value): ?float
```

Handles:
- Currency symbols
- Thousands separators (commas)
- Empty/null values
- Dash placeholders

### Pattern Matching

```php
protected function matchesPattern(string $header, array $patterns): bool
```

Case-insensitive partial matching for flexible column detection.

## Best Practices

### DO:
✅ Extend `BaseTransactionParser` for common functionality  
✅ Use descriptive parser identifiers (lowercase-with-dashes)  
✅ Provide clear parser names and descriptions  
✅ Implement comprehensive column detection patterns  
✅ Throw descriptive exceptions with user-friendly messages  
✅ Always return standardized transaction structure  
✅ Write thorough unit tests  
✅ Validate all required fields  
✅ Handle edge cases gracefully  

### DON'T:
❌ Modify database schema for new parsers  
❌ Change the output structure of parseRow()  
❌ Skip validation of required fields  
❌ Assume column positions are fixed  
❌ Use hardcoded column indexes  
❌ Return null for valid but empty amounts (return null specifically)  
❌ Forget to handle date format variations  

## Testing Your Parser

### Unit Tests

Test the parser in isolation:

```php
public function test_auto_detect_columns()
public function test_validate_mappings()
public function test_parse_row_with_valid_data()
public function test_parse_row_with_invalid_data()
public function test_parse_row_throws_exception_for_missing_required()
```

### Feature Tests

Test end-to-end import with your parser:

```php
public function test_can_import_with_custom_parser()
{
    $csvContent = "Date,Description,Custom,Balance\n";
    $csvContent .= "15/03/2024,Test Transaction,VALUE,900.00\n";
    
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    $response = $this->post(route('transactions.import'), [
        'bank_name' => 'Test Bank',
        'date_format' => 'd/m/Y',
        'file' => $file,
        'parser_type' => 'my-custom-format',
        'column_mappings' => json_encode([...]),
    ]);
    
    $response->assertStatus(200);
    $this->assertEquals(1, Transaction::count());
}
```

## Troubleshooting

### Parser Not Detected

- Check column name patterns in `autoDetectColumns()`
- Ensure patterns match actual file headers (case-insensitive)
- Verify parser is registered in factory

### Validation Errors

- Confirm all required fields are mapped
- Check field names match what's defined in `getRequiredFields()`
- Verify column indexes are correct

### Import Errors

- Check exception messages from `parseRow()`
- Verify date format matches file data
- Ensure amounts parse correctly
- Test with sample data first

## Future Extensions

Potential parser types that could be added:

- **Multi-currency parser** - Handle files with currency columns
- **Combined format parser** - Handle both separate and CR/DR in same file
- **Bank-specific parsers** - Optimized for specific bank formats
- **PDF parser** - Extract from PDF bank statements
- **QIF/OFX parsers** - Support financial data exchange formats

## Security Considerations

- Validate file size and type before parsing
- Sanitize all input data
- Use database transactions for atomic imports
- Log import errors for auditing
- Validate data types match expectations
- Handle malicious or malformed files gracefully

## Performance Tips

- Process files in batches for large datasets
- Use bulk inserts for multiple transactions
- Consider queuing for very large files
- Cache parser instances in factory
- Minimize database queries during parsing

## Contributing

When adding a new parser:

1. Create parser class with tests
2. Register in factory
3. Update validation rules
4. Add feature tests
5. Update this documentation
6. Ensure all tests pass (112+ tests)
7. Submit PR with clear description

## Support

For questions or issues:
- Check existing tests for examples
- Review parser implementations
- Consult Laravel and PHPUnit documentation
- Contact: Devnodes.in
