<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ContentMediaTypeKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DraftOptionTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class ContentMediaTypeKeywordParser extends KeywordParser
{
    use DraftOptionTrait;

    public function type(): string
    {
        return self::TYPE_STRING;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->optionAllowedForDraft('decodeContent', $info, $parser)) {
            return null;
        }

        $schema = $info->data();

        $resolver = $parser->getMediaTypeResolver();

        if (!$resolver || !$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_string($value)) {
            throw $this->keywordException('{keyword} must be a string', $info);
        }

        return new ContentMediaTypeKeyword($value, $resolver);
    }
}
