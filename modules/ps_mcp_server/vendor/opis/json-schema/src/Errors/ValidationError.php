<?php


namespace Opis\JsonSchema\Errors;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Info\DataInfo;

class ValidationError
{
    protected string $keyword;

    protected Schema $schema;

    protected DataInfo $data;

    protected array $args;

    protected string $message;

    
    protected array $subErrors;

    
    public function __construct(
        string $keyword,
        Schema $schema,
        DataInfo $data,
        string $message,
        array $args = [],
        array $subErrors = []
    ) {
        $this->keyword = $keyword;
        $this->schema = $schema;
        $this->data = $data;
        $this->message = $message;
        $this->args = $args;
        $this->subErrors = $subErrors;
    }

    public function keyword(): string
    {
        return $this->keyword;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function data(): DataInfo
    {
        return $this->data;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function subErrors(): array
    {
        return $this->subErrors;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}