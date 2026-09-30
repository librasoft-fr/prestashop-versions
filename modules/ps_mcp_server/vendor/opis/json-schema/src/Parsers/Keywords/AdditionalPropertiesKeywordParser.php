<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\AdditionalPropertiesKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class AdditionalPropertiesKeywordParser extends KeywordParser
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

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException("{keyword} must be a json schema (object or boolean)", $info);
        }

        return new AdditionalPropertiesKeyword($value);
    }
}