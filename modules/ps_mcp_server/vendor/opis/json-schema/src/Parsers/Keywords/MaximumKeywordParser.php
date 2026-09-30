<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};
use Opis\JsonSchema\Keywords\{
    ExclusiveMaximumDataKeyword,
    ExclusiveMaximumKeyword,
    MaximumDataKeyword,
    MaximumKeyword
};

class MaximumKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    protected ?string $exclusiveKeyword;

    
    public function __construct(string $keyword, ?string $exclusiveKeyword = null)
    {
        parent::__construct($keyword);
        $this->exclusiveKeyword = $exclusiveKeyword;
    }

    
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

        $exclusive = false;
        if ($parser->option('allowExclusiveMinMaxAsBool') &&
            $this->exclusiveKeyword !== null &&
            property_exists($schema, $this->exclusiveKeyword)) {
            $exclusive = $schema->{$this->exclusiveKeyword} === true;
        }

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return $exclusive
                    ? new ExclusiveMaximumDataKeyword($pointer)
                    : new MaximumDataKeyword($pointer);
            }
        }

        if (!is_int($value) && !is_float($value) || is_nan($value) || !is_finite($value)) {
            throw $this->keywordException('{keyword} must contain a valid number', $info);
        }

        return $exclusive
            ? new ExclusiveMaximumKeyword($value)
            : new MaximumKeyword($value);
    }
}