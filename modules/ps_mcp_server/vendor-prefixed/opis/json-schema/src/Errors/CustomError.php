<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Errors;

class CustomError extends \Exception
{
    protected array $args;

    public function __construct(string $message, array $args = [])
    {
        parent::__construct($message);
        $this->args = $args;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
