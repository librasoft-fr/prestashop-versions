<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\AllOfKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class AllOfKeywordParser extends KeywordParser
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

        $valid = 0;

        foreach ($value as $index => $item) {
            if ($item === false) {
                throw $this->keywordException("{keyword} contains false schema", $info);
            }
            if ($item === true) {
                $valid++;
                continue;
            }
            if (!is_object($item)) {
                throw $this->keywordException("{keyword}[{$index}] must be a json schema", $info);
            } elseif (!count(get_object_vars($item))) {
                $valid++;
            }
        }

        return $valid !== count($value) ? new AllOfKeyword($value) : null;
    }
}