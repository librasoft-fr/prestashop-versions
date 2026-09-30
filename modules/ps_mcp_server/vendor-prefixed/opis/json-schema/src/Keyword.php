<?php

namespace PsMcpServerDeps\Opis\JsonSchema;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;

interface Keyword
{
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError;
}
