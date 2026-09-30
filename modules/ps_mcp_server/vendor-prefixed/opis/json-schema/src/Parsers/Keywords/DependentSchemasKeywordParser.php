<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\DependentSchemasKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class DependentSchemasKeywordParser extends KeywordParser
{
    public function type(): string
    {
        return self::TYPE_OBJECT;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);
        if (!is_object($value)) {
            throw $this->keywordException('{keyword} must be an object', $info);
        }

        $valid = 0;
        $total = 0;

        foreach ($value as $name => $s) {
            ++$total;
            if (is_bool($s)) {
                if ($s) {
                    ++$valid;
                }
            } elseif (!is_object($s)) {
                throw $this->keywordException('{keyword} must be an object containing json schemas', $info);
            } elseif (!count(get_object_vars($s))) {
                ++$valid;
            }
        }

        if (!$total) {
            return null;
        }

        return $valid !== $total ? new DependentSchemasKeyword($value) : null;
    }
}
