<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};
use Opis\JsonSchema\Keywords\{MaxItemsDataKeyword, MaxItemsKeyword};

class MaxItemsKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    
    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new MaxItemsDataKeyword($pointer);
            }
        }

        if (!is_int($value) || $value < 0) {
            throw $this->keywordException("{keyword} most be a positive integer", $info);
        }

        return new MaxItemsKeyword($value);
    }
}