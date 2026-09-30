<?php


namespace Opis\JsonSchema\Parsers;

abstract class Vocabulary
{
    
    protected array $keywords;

    
    protected array $keywordValidators;

    
    protected array $pragmas;

    
    public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = [])
    {
        $this->keywords = $keywords;
        $this->keywordValidators = $keywordValidators;
        $this->pragmas = $pragmas;
    }

    
    public function keywords(): array
    {
        return $this->keywords;
    }

    
    public function keywordValidators(): array
    {
        return $this->keywordValidators;
    }

    
    public function pragmas(): array
    {
        return $this->pragmas;
    }

    
    public function appendKeyword(KeywordParser $keyword): self
    {
        $this->keywords[] = $keyword;
        return $this;
    }

    
    public function prependKeyword(KeywordParser $keyword): self
    {
        array_unshift($this->keywords, $keyword);
        return $this;
    }

    
    public function appendKeywordValidator(KeywordValidatorParser $keywordValidatorParser): self
    {
        $this->keywordValidators[] = $keywordValidatorParser;
        return $this;
    }

    
    public function prependKeywordValidator(KeywordValidatorParser $keywordValidator): self
    {
        array_unshift($this->keywordValidators, $keywordValidator);
        return $this;
    }

    
    public function appendPragma(PragmaParser $pragma): self
    {
        $this->pragmas[] = $pragma;
        return $this;
    }

    
    public function prependPragma(PragmaParser $pragma): self
    {
        array_unshift($this->pragmas, $pragma);
        return $this;
    }
}