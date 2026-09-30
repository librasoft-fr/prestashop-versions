<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\PatternPropertiesKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class PatternPropertiesKeywordParser extends KeywordParser
{
    public function type(): string
    {
        return self::TYPE_OBJECT;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);
        if (!is_object($value)) {
            throw $this->keywordException('{keyword} must be an object', $info);
        }

        $list = [];

        foreach ($value as $pattern => $item) {
            if (!Helper::isValidPattern($pattern)) {
                throw $this->keywordException('Each property name from {keyword} must be valid regex', $info);
            }

            if (!is_bool($item) && !is_object($item)) {
                throw $this->keywordException("{keyword}[{$pattern}] must be a json schema (object or boolean)", $info);
            }

            $list[$pattern] = $item;
        }

        return $list ? new PatternPropertiesKeyword($list) : null;
    }
}
