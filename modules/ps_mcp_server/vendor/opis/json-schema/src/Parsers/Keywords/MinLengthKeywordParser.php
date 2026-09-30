<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};
use Opis\JsonSchema\Keywords\{MinLengthDataKeyword, MinLengthKeyword};

class MinLengthKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    
    public function type(): string
    {
        return self::TYPE_STRING;
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
                return new MinLengthDataKeyword($pointer);
            }
        }

        if (!is_int($value) || $value < 0) {
            throw $this->keywordException("{keyword} must be a non-negative integer", $info);
        }

        if ($value === 0) {
            return null;
        }

        return new MinLengthKeyword($value);
    }
}