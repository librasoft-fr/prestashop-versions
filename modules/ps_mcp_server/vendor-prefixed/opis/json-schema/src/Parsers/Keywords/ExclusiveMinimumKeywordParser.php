<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ExclusiveMinimumDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ExclusiveMinimumKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class ExclusiveMinimumKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    public function type(): string
    {
        return self::TYPE_NUMBER;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (is_bool($value) && $parser->option('allowExclusiveMinMaxAsBool')) {
            return null;
        }

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new ExclusiveMinimumDataKeyword($pointer);
            }
        }

        if (!is_int($value) && !is_float($value) || is_nan($value) || !is_finite($value)) {
            throw $this->keywordException('{keyword} must contain a valid number', $info);
        }

        return new ExclusiveMinimumKeyword($value);
    }
}
