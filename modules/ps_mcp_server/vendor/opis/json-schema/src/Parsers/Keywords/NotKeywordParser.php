<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\NotKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class NotKeywordParser extends KeywordParser
{
    
    public function type(): string
    {
        return self::TYPE_AFTER;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (is_bool($value)) {
            if (!$value) {
                return null;
            }
        } elseif (!is_object($value)) {
            throw $this->keywordException("{keyword} must contain a json schema (object or boolean)", $info);
        }

        return new NotKeyword($value);
    }
}