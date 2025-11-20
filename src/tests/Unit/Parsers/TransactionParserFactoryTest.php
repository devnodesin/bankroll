<?php

namespace Tests\Unit\Parsers;

use App\Services\Parsers\TransactionParserFactory;
use App\Services\Parsers\StandardTransactionParser;
use App\Services\Parsers\CreditDebitTransactionParser;
use PHPUnit\Framework\TestCase;

class TransactionParserFactoryTest extends TestCase
{
    private TransactionParserFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new TransactionParserFactory();
    }

    public function test_get_parser_standard(): void
    {
        $parser = $this->factory->getParser('standard');
        $this->assertInstanceOf(StandardTransactionParser::class, $parser);
    }

    public function test_get_parser_credit_debit(): void
    {
        $parser = $this->factory->getParser('credit-debit');
        $this->assertInstanceOf(CreditDebitTransactionParser::class, $parser);
    }

    public function test_get_parser_throws_exception_for_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Parser 'invalid' not found");

        $this->factory->getParser('invalid');
    }

    public function test_get_all_parsers(): void
    {
        $parsers = $this->factory->getAllParsers();

        $this->assertIsArray($parsers);
        $this->assertArrayHasKey('standard', $parsers);
        $this->assertArrayHasKey('credit-debit', $parsers);
    }

    public function test_get_parser_options(): void
    {
        $options = $this->factory->getParserOptions();

        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        
        // Check structure of first option
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
        $this->assertArrayHasKey('description', $options[0]);
    }

    public function test_auto_detect_parser_for_standard_format(): void
    {
        $headers = ['Date', 'Description', 'Withdraw', 'Deposit', 'Balance'];
        $parser = $this->factory->autoDetectParser($headers);

        $this->assertInstanceOf(StandardTransactionParser::class, $parser);
        $this->assertEquals('standard', $parser->getIdentifier());
    }

    public function test_auto_detect_parser_for_credit_debit_format(): void
    {
        $headers = ['Date', 'Description', 'Amount', 'CR/DR', 'Balance'];
        $parser = $this->factory->autoDetectParser($headers);

        $this->assertInstanceOf(CreditDebitTransactionParser::class, $parser);
        $this->assertEquals('credit-debit', $parser->getIdentifier());
    }

    public function test_auto_detect_parser_defaults_to_standard_for_ambiguous(): void
    {
        $headers = ['Something', 'Random', 'Columns'];
        $parser = $this->factory->autoDetectParser($headers);

        $this->assertInstanceOf(StandardTransactionParser::class, $parser);
    }

    public function test_auto_detect_prefers_parser_with_all_required_fields(): void
    {
        // Headers that match standard format better
        $headers = ['Date', 'Description', 'Debit', 'Credit', 'Balance'];
        $parser = $this->factory->autoDetectParser($headers);

        $this->assertEquals('standard', $parser->getIdentifier());
    }
}
