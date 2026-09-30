<?php


namespace Opis\JsonSchema\KeywordValidators;

use Opis\JsonSchema\{ValidationContext, KeywordValidator};
use Opis\JsonSchema\Errors\ValidationError;

final class CallbackKeywordValidator implements KeywordValidator
{
    
    private $callback;

    
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    
    public function validate(ValidationContext $context): ?ValidationError
    {
        return ($this->callback)($context);
    }

    
    public function next(): ?KeywordValidator
    {
        return null;
    }

    
    public function setNext(?KeywordValidator $next): KeywordValidator
    {
        return $this;
    }
}