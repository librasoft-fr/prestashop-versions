<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\FormatDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\FormatKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\ResolverTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class FormatKeywordParser extends KeywordParser
{
    use ResolverTrait;
    use DataKeywordTrait;

    public function type(): string
    {
        return self::TYPE_BEFORE;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        $resolver = $parser->getFormatResolver();

        if (!$resolver || !$parser->option('allowFormats') || !$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new FormatDataKeyword($pointer, $resolver);
            }
        }

        if (!is_string($value)) {
            throw $this->keywordException('{keyword} must be a string', $info);
        }

        $list = $resolver->resolveAll($value);

        if (!$list) {
            return null;
        }

        return new FormatKeyword($value, $this->resolveSubTypes($list));
    }
}
