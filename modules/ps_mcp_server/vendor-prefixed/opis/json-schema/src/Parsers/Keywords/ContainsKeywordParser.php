<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ContainsKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class ContainsKeywordParser extends KeywordParser
{
    protected ?string $minContains = null;
    protected ?string $maxContains = null;

    public function __construct(string $keyword, ?string $minContains = null, ?string $maxContains = null)
    {
        parent::__construct($keyword);
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
    }

    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException('{keyword} must be a json schema (object or boolean)', $info);
        }

        $min = $max = null;

        if ($this->minContains && $this->keywordExists($schema, $this->minContains)) {
            $min = $this->keywordValue($schema, $this->minContains);
            if (!is_int($min) || $min < 0) {
                throw $this->keywordException('{keyword} must be a non-negative integer', $info, $this->minContains);
            }
        }

        if ($this->maxContains && $this->keywordExists($schema, $this->maxContains)) {
            $max = $this->keywordValue($schema, $this->maxContains);
            if (!is_int($max) || $max < 0) {
                throw $this->keywordException('{keyword} must be a non-negative integer', $info, $this->maxContains);
            }
            if ($min !== null && $max < $min) {
                throw $this->keywordException("{keyword} must be greater than {$this->minContains}", $info, $this->maxContains);
            }
        } elseif ($min === 0) {
            return null;
        }

        return new ContainsKeyword($value, $min, $max);
    }
}
