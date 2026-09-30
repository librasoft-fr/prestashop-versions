<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\ContentSchemaKeyword;
use Opis\JsonSchema\Parsers\{DraftOptionTrait, KeywordParser, SchemaParser};

class ContentSchemaKeywordParser extends KeywordParser
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

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_object($value)) {
            throw $this->keywordException("{keyword} must be a valid json schema object", $info);
        }

        return new ContentSchemaKeyword($value);
    }
}