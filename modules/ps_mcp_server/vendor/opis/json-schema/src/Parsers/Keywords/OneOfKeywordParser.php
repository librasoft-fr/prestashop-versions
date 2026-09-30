<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\OneOfKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class OneOfKeywordParser extends KeywordParser
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
                continue;
            }
            if ($item === true) {
                if (++$valid > 1) {
                    throw $this->keywordException("{keyword} contains multiple true values", $info);
                }
                continue;
            }
            if (!is_object($item)) {
                throw $this->keywordException("{keyword}[{$index}] must be a json schema", $info);
            } elseif (!count(get_object_vars($item))) {
                if (++$valid > 1) {
                    throw $this->keywordException("{keyword} contains multiple true values", $info);
                }
            }
        }

        return new OneOfKeyword($value);
    }
}