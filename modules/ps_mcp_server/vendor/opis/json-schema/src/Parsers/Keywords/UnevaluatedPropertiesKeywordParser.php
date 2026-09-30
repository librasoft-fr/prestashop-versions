<?php


namespace Opis\JsonSchema\Parsers\Keywords;


use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};
use Opis\JsonSchema\Keywords\UnevaluatedPropertiesKeyword;

class UnevaluatedPropertiesKeywordParser extends KeywordParser
{
    
    public function type(): string
    {
        return self::TYPE_AFTER_REF;
    }

    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema) || !$parser->option('allowUnevaluated')) {
            return null;
        }





        $value = $this->keywordValue($schema);

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException("{keyword} must be a json schema (object or boolean)", $info);
        }

        return new UnevaluatedPropertiesKeyword($value);
    }

    protected function makesSense(object $schema): bool
    {
        if (property_exists($schema, 'additionalProperties')) {
            return false;
        }

        return true;
    }
}