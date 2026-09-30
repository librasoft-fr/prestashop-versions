<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\DependenciesKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class DependenciesKeywordParser extends KeywordParser
{
    public function type(): string
    {
        return self::TYPE_OBJECT;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$parser->option('keepDependenciesKeyword') && !in_array($info->draft(), ['06', '07'])) {
            return null;
        }

        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);
        if (!is_object($value)) {
            throw $this->keywordException('{keyword} must be an object', $info);
        }

        $list = get_object_vars($value);

        foreach ($list as $name => $s) {
            if (is_array($s)) {
                if (!$s) {
                    unset($list[$name]);
                    continue;
                }
                foreach ($s as $p) {
                    if (!is_string($p)) {
                        throw $this->keywordException('{keyword} must be an object containing json schemas or arrays of property names', $info);
                    }
                }
                $list[$name] = array_unique($s);
            } elseif (is_bool($s)) {
                if ($s) {
                    unset($list[$name]);
                }
            } elseif (!is_object($s)) {
                throw $this->keywordException('{keyword} must be an object containing json schemas or arrays of property names', $info);
            }
        }

        return $list ? new DependenciesKeyword($list) : null;
    }
}
