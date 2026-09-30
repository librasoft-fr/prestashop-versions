<?php

namespace PsMcpServerDeps\Opis\JsonSchema;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;

interface SchemaValidator
{
    public function validate(ValidationContext $context): ?ValidationError;
}
