<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\MaxLengthDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\MaxLengthKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class MaxLengthKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    public function type(): string
    {
        return self::TYPE_STRING;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new MaxLengthDataKeyword($pointer);
            }
        }

        if (!is_int($value) || $value < 0) {
            throw $this->keywordException('{keyword} must be a non-negative integer', $info);
        }

        return new MaxLengthKeyword($value);
    }
}
