<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Schemas;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Exceptions\SchemaException;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

final class ExceptionSchema extends AbstractSchema
{
    private SchemaException $exception;

    public function __construct(SchemaInfo $info, SchemaException $exception)
    {
        parent::__construct($info);
        $this->exception = $exception;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        throw $this->exception;
    }
}
