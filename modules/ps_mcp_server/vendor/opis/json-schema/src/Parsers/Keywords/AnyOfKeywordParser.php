<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\AnyOfKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class AnyOfKeywordParser extends KeywordParser
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

        if (!is_array($value)) {
            throw $this->keywordException("{keyword} should be an array of json schemas", $info);
        }

        if (!$value) {
            throw $this->keywordException("{keyword} must have at least one element", $info);
        }

        $alwaysValid = false;

        foreach ($value as $index => $item) {
            if ($item === true) {
                $alwaysValid = true;
                continue;
            }
            if ($item === false) {
                continue;
            }
            if (!is_object($item)) {
                throw $this->keywordException("{keyword}[{$index}] must be a json schema", $info);
            } elseif (!count(get_object_vars($item))) {
                $alwaysValid = true;
            }
        }

        return new AnyOfKeyword($value, $alwaysValid);
    }
}