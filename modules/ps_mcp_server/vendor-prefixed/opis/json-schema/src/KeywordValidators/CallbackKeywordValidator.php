<?php

namespace PsMcpServerDeps\Opis\JsonSchema\KeywordValidators;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\KeywordValidator;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
