<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\SlotsKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class SlotsKeywordParser extends KeywordParser
{
    
    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$parser->option('allowSlots') || !$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (!is_object($value)) {
            throw $this->keywordException('{keyword} keyword value must be an object', $info);
        }

        $slots = [];
        foreach ($value as $name => $fallback) {
            if (!is_string($name) || $name === '') {
                continue;
            }
            if (is_bool($fallback) || is_string($fallback) || is_object($fallback)) {
                $slots[$name] = $fallback;
            }
        }

        return $slots ? new SlotsKeyword($slots) : null;
    }
}