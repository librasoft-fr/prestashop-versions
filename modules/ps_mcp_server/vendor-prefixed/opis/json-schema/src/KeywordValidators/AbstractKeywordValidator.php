<?php

namespace PsMcpServerDeps\Opis\JsonSchema\KeywordValidators;

use PsMcpServerDeps\Opis\JsonSchema\KeywordValidator;

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
