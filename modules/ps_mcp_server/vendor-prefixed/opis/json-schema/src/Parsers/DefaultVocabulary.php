<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\FiltersKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\SlotsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordValidators\PragmaKeywordValidatorParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas\CastPragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas\GlobalsPragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas\MaxErrorsPragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas\SlotsPragmaParser;

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
