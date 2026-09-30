<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\AdditionalItemsKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class AdditionalItemsKeywordParser extends KeywordParser
{
    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$parser->option('keepAdditionalItemsKeyword') && $info->draft() === '2020-12') {
            return null;
        }

        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        if (!property_exists($schema, 'items') || !is_array($schema->items)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException('{keyword} must be a json schema (object or boolean)', $info);
        }

        return new AdditionalItemsKeyword($value, count($schema->items));
    }
}
