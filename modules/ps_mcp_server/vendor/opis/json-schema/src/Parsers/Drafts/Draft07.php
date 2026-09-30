<?php


namespace Opis\JsonSchema\Parsers\Drafts;

use Opis\JsonSchema\Parsers\Keywords\IfThenElseKeywordParser;

class Draft07 extends Draft06
{
    
    public function version(): string
    {
        return '07';
    }

    
    protected function getKeywordParsers(): array
    {
        $keywords = parent::getKeywordParsers();

        $keywords[] = new IfThenElseKeywordParser('if', 'then', 'else');

        return $keywords;
    }
}