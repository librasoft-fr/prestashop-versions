<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ExclusiveMinimumDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ExclusiveMinimumKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\MinimumDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\MinimumKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class MinimumKeywordParser extends KeywordParser
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
        if ($parser->option('allowExclusiveMinMaxAsBool')
            && $this->exclusiveKeyword !== null
            && property_exists($schema, $this->exclusiveKeyword)) {
            $exclusive = $schema->{$this->exclusiveKeyword} === true;
        }

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return $exclusive
                    ? new ExclusiveMinimumDataKeyword($pointer)
                    : new MinimumDataKeyword($pointer);
            }
        }

        if (!is_int($value) && !is_float($value) || is_nan($value) || !is_finite($value)) {
            throw $this->keywordException('{keyword} must contain a valid number', $info);
        }

        return $exclusive
            ? new ExclusiveMinimumKeyword($value)
            : new MinimumKeyword($value);
    }
}
