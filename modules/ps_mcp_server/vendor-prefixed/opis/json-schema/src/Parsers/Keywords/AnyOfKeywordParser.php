<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\AnyOfKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class AnyOfKeywordParser extends KeywordParser
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

        if (!is_array($value)) {
            throw $this->keywordException('{keyword} should be an array of json schemas', $info);
        }

        if (!$value) {
            throw $this->keywordException('{keyword} must have at least one element', $info);
        }

        $alwaysValid = false;

        foreach ($value as $index => $item) {
            if ($item === true) {
                $alwaysValid = true;
                continue;
            }
            if ($item === false) {
                continue;
            }
            if (!is_object($item)) {
                throw $this->keywordException("{keyword}[{$index}] must be a json schema", $info);
            } elseif (!count(get_object_vars($item))) {
                $alwaysValid = true;
            }
        }

        return new AnyOfKeyword($value, $alwaysValid);
    }
}
