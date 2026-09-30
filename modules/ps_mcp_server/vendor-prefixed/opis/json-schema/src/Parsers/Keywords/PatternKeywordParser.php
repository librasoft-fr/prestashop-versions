<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\PatternDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\PatternKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class PatternKeywordParser extends KeywordParser
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
                return new PatternDataKeyword($pointer);
            }
        }

        if (!is_string($value)) {
            throw $this->keywordException('{keyword} value must be a string', $info);
        }

        if (!Helper::isValidPattern($value)) {
            throw $this->keywordException('{keyword} value must be a valid regex', $info);
        }

        return new PatternKeyword($value);
    }
}
