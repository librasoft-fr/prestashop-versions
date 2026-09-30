<?php


namespace Opis\JsonSchema\KeywordValidators;

use Opis\JsonSchema\KeywordValidator;

abstract class AbstractKeywordValidator implements KeywordValidator
{

    protected ?KeywordValidator $next = null;

    
    public function next(): ?KeywordValidator
    {
        return $this->next;
    }

    
    public function setNext(?KeywordValidator $next): KeywordValidator
    {
        $this->next = $next;

        return $this;
    }
}