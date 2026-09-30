<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\{Helper, Keyword};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\PatternPropertiesKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class PatternPropertiesKeywordParser extends KeywordParser
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

        foreach ($value as $pattern => $item) {
            if (!Helper::isValidPattern($pattern)) {
                throw $this->keywordException("Each property name from {keyword} must be valid regex", $info);
            }

            if (!is_bool($item) && !is_object($item)) {
                throw $this->keywordException("{keyword}[{$pattern}] must be a json schema (object or boolean)", $info);
            }

            $list[$pattern] = $item;
        }

        return $list ? new PatternPropertiesKeyword($list) : null;
    }
}