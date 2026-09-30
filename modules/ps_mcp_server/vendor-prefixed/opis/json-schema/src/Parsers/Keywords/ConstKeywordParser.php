<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ConstDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ConstKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class ConstKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    public function type(): string
    {
        return self::TYPE_BEFORE;
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
                return new ConstDataKeyword($pointer);
            }
        }

        $type = Helper::getJsonType($value);
        if ($type === null) {
            throw $this->keywordException('{keyword} contains unknown json data type', $info);
        }

        if (isset($shared->types)) {
            if (!Helper::jsonTypeMatches($type, $shared->types)) {
                throw $this->keywordException("{keyword} contains a value that doesn't match the type keyword", $info);
            }
        } else {
            $shared->types = [$type];
        }

        return new ConstKeyword($value);
    }
}
