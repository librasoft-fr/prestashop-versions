<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\DependentSchemasKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class DependentSchemasKeywordParser extends KeywordParser
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
        if (!is_object($value)) {
            throw $this->keywordException("{keyword} must be an object", $info);
        }

        $valid = 0;
        $total = 0;

        foreach ($value as $name => $s) {
            $total++;
            if (is_bool($s)) {
                if ($s) {
                    $valid++;
                }
            } elseif (!is_object($s)) {
                throw $this->keywordException("{keyword} must be an object containing json schemas", $info);
            } elseif (!count(get_object_vars($s))) {
                $valid++;
            }
        }

        if (!$total) {
            return null;
        }

        return $valid !== $total ? new DependentSchemasKeyword($value) : null;
    }
}