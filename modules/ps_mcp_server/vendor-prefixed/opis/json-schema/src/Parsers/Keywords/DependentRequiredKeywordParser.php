<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\DependentRequiredKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class DependentRequiredKeywordParser extends KeywordParser
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
        foreach ($value as $name => $s) {
            if (!is_array($s)) {
                throw $this->keywordException('{keyword} must be an object containing json schemas or arrays of property names', $info);
            }
            if (!$s) {
                continue;
            }
            foreach ($s as $p) {
                if (!is_string($p)) {
                    throw $this->keywordException('{keyword} must be an object containing arrays of property names', $info);
                }
            }
            $list[$name] = array_unique($s);
        }

        return $list ? new DependentRequiredKeyword($list) : null;
    }
}
