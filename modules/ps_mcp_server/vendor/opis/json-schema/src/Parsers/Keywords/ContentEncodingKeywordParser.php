<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\ContentEncodingKeyword;
use Opis\JsonSchema\Parsers\{DraftOptionTrait, KeywordParser, SchemaParser};

class ContentEncodingKeywordParser extends KeywordParser
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

        $resolver = $parser->getContentEncodingResolver();

        if (!$resolver || !$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_string($value)) {
            throw $this->keywordException("{keyword} must be a string", $info);
        }

        return new ContentEncodingKeyword(strtolower($value), $resolver);
    }
}