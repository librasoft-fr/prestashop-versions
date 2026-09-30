<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\{Helper, Keyword};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\{PatternKeyword, PatternDataKeyword};
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};

class PatternKeywordParser extends KeywordParser
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
                return new PatternDataKeyword($pointer);
            }
        }

        if (!is_string($value)) {
            throw $this->keywordException("{keyword} value must be a string", $info);
        }

        if (!Helper::isValidPattern($value)) {
            throw $this->keywordException("{keyword} value must be a valid regex", $info);
        }

        return new PatternKeyword($value);
    }
}