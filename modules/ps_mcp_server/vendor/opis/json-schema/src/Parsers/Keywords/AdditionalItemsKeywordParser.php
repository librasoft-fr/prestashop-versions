<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\AdditionalItemsKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class AdditionalItemsKeywordParser extends KeywordParser
{
    
    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$parser->option('keepAdditionalItemsKeyword') && $info->draft() === '2020-12') {
            return null;
        }

        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        if (!property_exists($schema, 'items') || !is_array($schema->items)) {
            
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException("{keyword} must be a json schema (object or boolean)", $info);
        }

        return new AdditionalItemsKeyword($value, count($schema->items));
    }
}