<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\IfThenElseKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class IfThenElseKeywordParser extends KeywordParser
{
    protected string $then;

    protected string $else;

    public function __construct(string $if, string $then, string $else)
    {
        parent::__construct($if);
        $this->then = $then;
        $this->else = $else;
    }

    public function type(): string
    {
        return self::TYPE_AFTER;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $if = $this->keywordValue($schema);
        if (!$this->isJsonSchema($if)) {
            throw $this->keywordException('{keyword} keyword must be a json schema', $info);
        }

        $then = true;
        if (property_exists($schema, $this->then)) {
            $then = $schema->{$this->then};
        }
        if (!$this->isJsonSchema($then)) {
            throw $this->keywordException('{keyword} keyword must be a json schema', $info, $this->then);
        }

        $else = true;
        if (property_exists($schema, $this->else)) {
            $else = $schema->{$this->else};
        }
        if (!$this->isJsonSchema($else)) {
            throw $this->keywordException('{keyword} keyword must be a json schema', $info, $this->else);
        }

        if ($if === true) {
            if ($then === true) {
                return null;
            }
            $else = true;
        } elseif ($if === false) {
            if ($else === true) {
                return null;
            }
            $then = true;
        } elseif ($then === true && $else === true) {
            return null;
        }

        return new IfThenElseKeyword($if, $then, $else);
    }

    protected function isJsonSchema($value): bool
    {
        return is_bool($value) || is_object($value);
    }
}
