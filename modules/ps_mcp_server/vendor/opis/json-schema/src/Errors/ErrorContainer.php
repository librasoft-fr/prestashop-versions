<?php


namespace Opis\JsonSchema\Errors;

use Countable, Iterator;

class ErrorContainer implements Countable, Iterator
{

    protected int $maxErrors;

    
    protected array $errors = [];

    
    public function __construct(int $max_errors = 1)
    {
        if ($max_errors < 0) {
            $max_errors = PHP_INT_MAX;
        } elseif ($max_errors === 0) {
            $max_errors = 1;
        }

        $this->maxErrors = $max_errors;
    }

    
    public function maxErrors(): int
    {
        return $this->maxErrors;
    }

    
    public function add(ValidationError $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    
    public function all(): array
    {
        return $this->errors;
    }

    
    public function first(): ?ValidationError
    {
        if (!$this->errors) {
            return null;
        }

        return reset($this->errors);
    }

    
    public function isFull(): bool
    {
        return count($this->errors) >= $this->maxErrors;
    }

    
    public function isEmpty(): bool
    {
        return !$this->errors;
    }

    
    public function count(): int
    {
        return count($this->errors);
    }

    
    public function current(): ?ValidationError
    {
        return current($this->errors) ?: null;
    }

    
    #[\ReturnTypeWillChange]
    public function next(): ?ValidationError
    {
        return next($this->errors) ?: null;
    }

    
    public function key(): ?int
    {
        return key($this->errors);
    }

    
    public function valid(): bool
    {
        return key($this->errors) !== null;
    }

    
    #[\ReturnTypeWillChange]
    public function rewind(): ?ValidationError
    {
        return reset($this->errors) ?: null;
    }
}
