<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\PropertiesKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class PropertiesKeywordParser extends KeywordParser
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

        $list = [];

        foreach ($value as $name => $s) {
            if (!is_bool($s) && !is_object($s)) {
                throw $this->keywordException("{keyword}[{$name}] must be a json schema (object or boolean)", $info);
            }

            $list[$name] = $s;
        }

        return $list ? new PropertiesKeyword($list) : null;
    }
}