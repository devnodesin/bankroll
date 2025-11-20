<?php

namespace Tests\Unit\Parsers;

use App\Services\Parsers\CreditDebitTransactionParser;
use PHPUnit\Framework\TestCase;

class CreditDebitTransactionParserTest extends TestCase
{
    private CreditDebitTransactionParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CreditDebitTransactionParser();
    }

    public function test_get_identifier(): void
    {
        $this->assertEquals('credit-debit', $this->parser->getIdentifier());
    }

    public function test_get_name(): void
    {
        $this->assertEquals('Credit/Debit Format', $this->parser->getName());
    }

    public function test_get_description(): void
    {
        $this->assertNotEmpty($this->parser->getDescription());
    }

    public function test_get_required_fields(): void
    {
        $required = $this->parser->getRequiredFields();
        
        $this->assertContains('date', $required);
        $this->assertContains('description', $required);
        $this->assertContains('amount', $required);
        $this->assertContains('type', $required);
        $this->assertContains('balance', $required);
    }

    public function test_auto_detect_columns_crdr_format(): void
    {
        $headers = ['Date', 'Description', 'Amount', 'CR/DR', 'Balance'];
        $mappings = $this->parser->autoDetectColumns($headers);

        $this->assertEquals(0, $mappings['date']);
        $this->assertEquals(1, $mappings['description']);
        $this->assertEquals(2, $mappings['amount']);
        $this->assertEquals(3, $mappings['type']);
        $this->assertEquals(4, $mappings['balance']);
    }

    public function test_auto_detect_columns_with_variations(): void
    {
        $headers = ['Transaction Date', 'Particulars', 'Transaction Amount', 'Type', 'Closing Balance'];
        $mappings = $this->parser->autoDetectColumns($headers);

        $this->assertEquals(0, $mappings['date']);
        $this->assertEquals(1, $mappings['description']);
        $this->assertEquals(2, $mappings['amount']);
        $this->assertEquals(3, $mappings['type']);
        $this->assertEquals(4, $mappings['balance']);
    }

    public function test_validate_mappings_with_all_required(): void
    {
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $missing = $this->parser->validateMappings($mappings);
        $this->assertEmpty($missing);
    }

    public function test_validate_mappings_with_missing_required(): void
    {
        $mappings = [
            'date' => 0,
            'amount' => 2,
        ];

        $missing = $this->parser->validateMappings($mappings);
        $this->assertContains('description', $missing);
        $this->assertContains('type', $missing);
        $this->assertContains('balance', $missing);
    }

    public function test_parse_row_with_debit(): void
    {
        $row = ['15/03/2024', 'ATM Withdrawal', '500.00', 'DR', '4500.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertEquals('Test Bank', $result['bank_name']);
        $this->assertEquals('ATM Withdrawal', $result['description']);
        $this->assertEquals(500.00, $result['withdraw']);
        $this->assertNull($result['deposit']);
        $this->assertEquals(4500.00, $result['balance']);
        $this->assertEquals(2024, $result['year']);
        $this->assertEquals(3, $result['month']);
    }

    public function test_parse_row_with_credit(): void
    {
        $row = ['15/03/2024', 'Salary Credit', '5000.00', 'CR', '9500.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertNull($result['withdraw']);
        $this->assertEquals(5000.00, $result['deposit']);
        $this->assertEquals(9500.00, $result['balance']);
    }

    public function test_parse_row_with_credit_variation(): void
    {
        $row = ['15/03/2024', 'Payment', '100.00', 'CREDIT', '9600.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertNull($result['withdraw']);
        $this->assertEquals(100.00, $result['deposit']);
    }

    public function test_parse_row_with_debit_variation(): void
    {
        $row = ['15/03/2024', 'Purchase', '50.00', 'DEBIT', '9550.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertEquals(50.00, $result['withdraw']);
        $this->assertNull($result['deposit']);
    }

    public function test_parse_row_throws_exception_for_invalid_type(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Type field must be CR/Credit');

        $row = ['15/03/2024', 'Test', '100.00', 'INVALID', '900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }

    public function test_parse_row_throws_exception_for_missing_amount(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Amount field is required');

        $row = ['15/03/2024', 'Test', '', 'CR', '900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }

    public function test_parse_row_throws_exception_for_missing_balance(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Balance field is required');

        $row = ['15/03/2024', 'Test', '100.00', 'CR', ''];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }

    public function test_parse_row_throws_exception_for_invalid_date(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not in the expected format');

        $row = ['invalid-date', 'Test', '100.00', 'CR', '900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }
}
