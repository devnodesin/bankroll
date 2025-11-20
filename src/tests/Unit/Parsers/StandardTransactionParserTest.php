<?php

namespace Tests\Unit\Parsers;

use App\Services\Parsers\StandardTransactionParser;
use PHPUnit\Framework\TestCase;

class StandardTransactionParserTest extends TestCase
{
    private StandardTransactionParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new StandardTransactionParser();
    }

    public function test_get_identifier(): void
    {
        $this->assertEquals('standard', $this->parser->getIdentifier());
    }

    public function test_get_name(): void
    {
        $this->assertEquals('Standard Format', $this->parser->getName());
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
        $this->assertContains('balance', $required);
    }

    public function test_auto_detect_columns_standard_headers(): void
    {
        $headers = ['Date', 'Description', 'Withdraw', 'Deposit', 'Balance'];
        $mappings = $this->parser->autoDetectColumns($headers);

        $this->assertEquals(0, $mappings['date']);
        $this->assertEquals(1, $mappings['description']);
        $this->assertEquals(2, $mappings['withdraw']);
        $this->assertEquals(3, $mappings['deposit']);
        $this->assertEquals(4, $mappings['balance']);
    }

    public function test_auto_detect_columns_with_variations(): void
    {
        $headers = ['Transaction Date', 'Particulars', 'Debit', 'Credit', 'Closing Balance'];
        $mappings = $this->parser->autoDetectColumns($headers);

        $this->assertEquals(0, $mappings['date']);
        $this->assertEquals(1, $mappings['description']);
        $this->assertEquals(2, $mappings['withdraw']);
        $this->assertEquals(3, $mappings['deposit']);
        $this->assertEquals(4, $mappings['balance']);
    }

    public function test_auto_detect_returns_null_for_unmapped_columns(): void
    {
        $headers = ['Date', 'Something', 'Balance'];
        $mappings = $this->parser->autoDetectColumns($headers);

        $this->assertEquals(0, $mappings['date']);
        $this->assertNull($mappings['description']);
        $this->assertNull($mappings['withdraw']);
        $this->assertNull($mappings['deposit']);
        $this->assertEquals(2, $mappings['balance']);
    }

    public function test_validate_mappings_with_all_required(): void
    {
        $mappings = [
            'date' => 0,
            'description' => 1,
            'balance' => 4,
        ];

        $missing = $this->parser->validateMappings($mappings);
        $this->assertEmpty($missing);
    }

    public function test_validate_mappings_with_missing_required(): void
    {
        $mappings = [
            'date' => 0,
            'withdraw' => 2,
        ];

        $missing = $this->parser->validateMappings($mappings);
        $this->assertContains('description', $missing);
        $this->assertContains('balance', $missing);
    }

    public function test_parse_row_with_withdraw(): void
    {
        $row = ['15/03/2024', 'Test Transaction', '100.00', '', '900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertEquals('Test Bank', $result['bank_name']);
        $this->assertEquals('Test Transaction', $result['description']);
        $this->assertEquals(100.00, $result['withdraw']);
        $this->assertNull($result['deposit']);
        $this->assertEquals(900.00, $result['balance']);
        $this->assertEquals(2024, $result['year']);
        $this->assertEquals(3, $result['month']);
    }

    public function test_parse_row_with_deposit(): void
    {
        $row = ['15/03/2024', 'Salary', '', '5000.00', '5900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ];

        $result = $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');

        $this->assertNull($result['withdraw']);
        $this->assertEquals(5000.00, $result['deposit']);
        $this->assertEquals(5900.00, $result['balance']);
    }

    public function test_parse_row_throws_exception_for_invalid_date(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not in the expected format');

        $row = ['invalid-date', 'Test', '100', '', '900'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }

    public function test_parse_row_throws_exception_for_missing_balance(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Balance field is required');

        $row = ['15/03/2024', 'Test', '100', '', ''];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }

    public function test_parse_row_throws_exception_for_both_amounts_empty(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Either Withdraw or Deposit must have a value');

        $row = ['15/03/2024', 'Test', '', '', '900.00'];
        $mappings = [
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ];

        $this->parser->parseRow($row, $mappings, 'd/m/Y', 'Test Bank');
    }
}
