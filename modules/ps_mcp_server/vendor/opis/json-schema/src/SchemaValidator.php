<?php


namespace Opis\JsonSchema;

use Opis\JsonSchema\Errors\ValidationError;

interface SchemaValidator
{
    
    public function validate(ValidationContext $context): ?ValidationError;
}