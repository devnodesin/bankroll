<?php

namespace App\Services\Parsers;

/**
 * Factory for creating transaction parser instances
 * 
 * This factory manages all available parsers and provides methods to:
 * - Get a specific parser by identifier
 * - Get all available parsers
 * - Auto-select the best parser for a given file
 */
class TransactionParserFactory
{
    /**
     * @var array<string, TransactionParserInterface> Available parsers
     */
    private array $parsers = [];

    /**
     * Constructor - register all available parsers
     */
    public function __construct()
    {
        $this->registerParser(new StandardTransactionParser());
        $this->registerParser(new CreditDebitTransactionParser());
    }

    /**
     * Register a parser
     * 
     * @param TransactionParserInterface $parser The parser to register
     * @return void
     */
    public function registerParser(TransactionParserInterface $parser): void
    {
        $this->parsers[$parser->getIdentifier()] = $parser;
    }

    /**
     * Get a parser by identifier
     * 
     * @param string $identifier The parser identifier
     * @return TransactionParserInterface
     * @throws \InvalidArgumentException If parser not found
     */
    public function getParser(string $identifier): TransactionParserInterface
    {
        if (!isset($this->parsers[$identifier])) {
            throw new \InvalidArgumentException("Parser '{$identifier}' not found");
        }

        return $this->parsers[$identifier];
    }

    /**
     * Get all available parsers
     * 
     * @return array<string, TransactionParserInterface> Array of parsers keyed by identifier
     */
    public function getAllParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Get parser information for display (e.g., in UI dropdown)
     * 
     * @return array Array of parser info: [['id' => '...', 'name' => '...', 'description' => '...'], ...]
     */
    public function getParserOptions(): array
    {
        $options = [];
        foreach ($this->parsers as $parser) {
            $options[] = [
                'id' => $parser->getIdentifier(),
                'name' => $parser->getName(),
                'description' => $parser->getDescription(),
            ];
        }
        return $options;
    }

    /**
     * Auto-detect the best parser for given headers
     * 
     * Tries to determine which parser is most suitable based on the file headers.
     * Returns the parser with the most detected columns.
     * 
     * @param array $headers Column headers from the file
     * @return TransactionParserInterface The best matching parser
     */
    public function autoDetectParser(array $headers): TransactionParserInterface
    {
        $bestParser = null;
        $bestScore = 0;

        foreach ($this->parsers as $parser) {
            $mappings = $parser->autoDetectColumns($headers);
            $score = 0;
            
            // Count how many columns were successfully mapped
            foreach ($mappings as $value) {
                if ($value !== null) {
                    $score++;
                }
            }

            // Prefer parsers that map all required fields
            $requiredFields = $parser->getRequiredFields();
            $allRequiredMapped = true;
            foreach ($requiredFields as $field) {
                if (!isset($mappings[$field]) || $mappings[$field] === null) {
                    $allRequiredMapped = false;
                    break;
                }
            }

            // Bonus points if all required fields are mapped
            if ($allRequiredMapped) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestParser = $parser;
            }
        }

        // Default to standard parser if no good match
        return $bestParser ?? $this->getParser('standard');
    }
}
