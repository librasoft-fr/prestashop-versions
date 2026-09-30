<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\UnevaluatedItemsKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class UnevaluatedItemsKeywordParser extends KeywordParser
{
    public function type(): string
    {
        return self::TYPE_AFTER_REF;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema) || !$parser->option('allowUnevaluated')) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException('{keyword} must be a json schema (object or boolean)', $info);
        }

        return new UnevaluatedItemsKeyword($value);
    }

    protected function makesSense(object $schema): bool
    {
        if (property_exists($schema, 'additionalItems')) {
            return false;
        }

        if (property_exists($schema, 'items') && !is_array($schema->items)) {
            return false;
        }

        return true;
    }
}
