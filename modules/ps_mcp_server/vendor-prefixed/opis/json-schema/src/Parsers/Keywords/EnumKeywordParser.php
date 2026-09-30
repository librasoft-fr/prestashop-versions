<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\EnumDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\EnumKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class EnumKeywordParser extends KeywordParser
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
                return new EnumDataKeyword($pointer);
            }
        }

        if (!is_array($value) || !$value) {
            throw $this->keywordException('{keyword} must be a non-empty array', $info);
        }

        $hasConst = property_exists($schema, 'const');
        $constMatched = false;

        $allowedTypes = isset($shared->types) ? $shared->types : null;
        $foundTypes = [];
        $list = [];
        foreach ($value as $item) {
            $type = Helper::getJsonType($item);
            if ($type === null) {
                throw $this->keywordException('{keyword} contains invalid json data type', $info);
            }

            if ($allowedTypes && !Helper::jsonTypeMatches($type, $allowedTypes)) {
                continue;
            }

            if ($hasConst && Helper::equals($item, $schema->const)) {
                $constMatched = true;
                break;
            }

            if (!in_array($type, $foundTypes)) {
                $foundTypes[] = $type;
            }

            $list[] = $item;
        }

        if ($hasConst) {
            if ($constMatched) {
                return null;
            }
            throw $this->keywordException('{keyword} does not contain the value of const keyword', $info);
        }

        if ($foundTypes) {
            if ($allowedTypes === null) {
                $shared->types = $foundTypes;
            } else {
                $shared->types = array_unique(array_merge($shared->types, $foundTypes));
            }
        }

        return new EnumKeyword($list);
    }
}
