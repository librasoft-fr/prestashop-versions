<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\{Keyword, Helper};
use Opis\JsonSchema\Keywords\{ConstDataKeyword, ConstKeyword};
use Opis\JsonSchema\Parsers\{
    KeywordParser, DataKeywordTrait, SchemaParser
};

class ConstKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    
    public function type(): string
    {
        return self::TYPE_BEFORE;
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
                return new ConstDataKeyword($pointer);
            }
        }

        $type = Helper::getJsonType($value);
        if ($type === null) {
            throw $this->keywordException("{keyword} contains unknown json data type", $info);
        }

        if (isset($shared->types)) {
            if (!Helper::jsonTypeMatches($type, $shared->types)) {
                throw $this->keywordException("{keyword} contains a value that doesn't match the type keyword", $info);
            }
        } else {
            $shared->types = [$type];
        }

        return new ConstKeyword($value);
    }

}