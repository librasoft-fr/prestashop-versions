<?php


namespace Opis\JsonSchema;

use Opis\JsonSchema\Errors\ValidationError;

class ValidationResult
{
    protected ?ValidationError $error;

    public function __construct(?ValidationError $error)
    {
        $this->error = $error;
    }

    public function error(): ?ValidationError
    {
        return $this->error;
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function __toString(): string
    {
        if ($this->error) {
            return $this->error->message();
        }
        return '';
    }
}