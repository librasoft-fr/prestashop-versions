<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};
use Opis\JsonSchema\Keywords\{
    ExclusiveMinimumDataKeyword,
    ExclusiveMinimumKeyword
};

class ExclusiveMinimumKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    
    public function type(): string
    {
        return self::TYPE_NUMBER;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if (is_bool($value) && $parser->option('allowExclusiveMinMaxAsBool')) {
            return null;
        }

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new ExclusiveMinimumDataKeyword($pointer);
            }
        }

        if (!is_int($value) && !is_float($value) || is_nan($value) || !is_finite($value)) {
            throw $this->keywordException('{keyword} must contain a valid number', $info);
        }

        return new ExclusiveMinimumKeyword($value);
    }
}