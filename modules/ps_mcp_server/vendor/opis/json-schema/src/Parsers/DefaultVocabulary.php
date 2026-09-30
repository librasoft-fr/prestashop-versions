<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Parsers\Keywords\{
    FiltersKeywordParser,
    SlotsKeywordParser
};
use Opis\JsonSchema\Parsers\Pragmas\{CastPragmaParser, GlobalsPragmaParser,
    MaxErrorsPragmaParser, SlotsPragmaParser};
use Opis\JsonSchema\Parsers\KeywordValidators\PragmaKeywordValidatorParser;

class DefaultVocabulary extends Vocabulary
{
    
    public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = [])
    {
        $keywords = array_merge($keywords, [
            new FiltersKeywordParser('$filters'),
            new SlotsKeywordParser('$slots'),
        ]);

        $keywordValidators = array_merge([
            
            new PragmaKeywordValidatorParser('$pragma'),
        ], $keywordValidators);

        $pragmas = array_merge($pragmas, [
            new MaxErrorsPragmaParser('maxErrors'),
            new SlotsPragmaParser('slots'),
            new GlobalsPragmaParser('globals'),
            new CastPragmaParser('cast'),
        ]);

        parent::__construct($keywords, $keywordValidators, $pragmas);
    }
}