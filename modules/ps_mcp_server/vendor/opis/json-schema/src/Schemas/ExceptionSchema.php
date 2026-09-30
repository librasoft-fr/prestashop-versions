<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\SchemaException;

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