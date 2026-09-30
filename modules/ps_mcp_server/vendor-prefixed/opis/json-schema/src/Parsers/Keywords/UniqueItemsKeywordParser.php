<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\UniqueItemsDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\UniqueItemsKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class UniqueItemsKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

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

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new UniqueItemsDataKeyword($pointer);
            }
        }

        if (!is_bool($value)) {
            throw $this->keywordException('{keyword} must be a boolean', $info);
        }

        return $value ? new UniqueItemsKeyword() : null;
    }
}
