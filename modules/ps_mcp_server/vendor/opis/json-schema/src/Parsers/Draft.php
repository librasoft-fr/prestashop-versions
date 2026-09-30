<?php


namespace Opis\JsonSchema\Parsers;

abstract class Draft extends Vocabulary
{
    
    public function __construct(?Vocabulary $extraVocabulary = null)
    {
        $keywords = $this->getKeywordParsers();
        $keywordValidators = $this->getKeywordValidatorParsers();
        $pragmas = $this->getPragmaParsers();

        if ($extraVocabulary) {
            $keywords = array_merge($keywords, $extraVocabulary->keywords());
            $keywordValidators = array_merge($keywordValidators, $extraVocabulary->keywordValidators());
            $pragmas = array_merge($pragmas, $extraVocabulary->pragmas());
        }

        array_unshift($keywords, $this->getRefKeywordParser());

        parent::__construct($keywords, $keywordValidators, $pragmas);
    }

    
    abstract public function version(): string;

    
    abstract public function allowKeywordsAlongsideRef(): bool;

    
    abstract public function supportsAnchorId(): bool;

    
    abstract protected function getRefKeywordParser(): KeywordParser;

    
    abstract protected function getKeywordParsers(): array;

    
    protected function getKeywordValidatorParsers(): array
    {
        return [];
    }

    
    protected function getPragmaParsers(): array
    {
        return [];
    }
}