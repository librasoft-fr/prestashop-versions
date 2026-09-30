<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\NotKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class NotKeywordParser extends KeywordParser
{
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

        $value = $this->keywordValue($schema);

        if (is_bool($value)) {
            if (!$value) {
                return null;
            }
        } elseif (!is_object($value)) {
            throw $this->keywordException('{keyword} must contain a json schema (object or boolean)', $info);
        }

        return new NotKeyword($value);
    }
}
