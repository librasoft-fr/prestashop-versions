<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\TypeKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class TypeKeywordParser extends KeywordParser
{
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

        $type = $this->keywordValue($schema);

        if (is_string($type)) {
            $type = [$type];
        } elseif (!is_array($type)) {
            throw $this->keywordException('{keyword} can only be a string or an array of string', $info);
        }

        foreach ($type as $t) {
            if (!Helper::isValidJsonType($t)) {
                throw $this->keywordException("{keyword} contains invalid json type: {$t}", $info);
            }
        }

        $type = array_unique($type);

        if (!isset($shared->types)) {
            $shared->types = $type;
        } else {
            $shared->types = array_unique(array_merge($shared->types, $type));
        }

        $count = count($type);

        if ($count === 0) {
            throw $this->keywordException('{keyword} cannot be an empty array', $info);
        } elseif ($count === 1) {
            $type = reset($type);
        }

        return new TypeKeyword($type);
    }
}
