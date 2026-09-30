<?php


namespace Opis\JsonSchema;

use Opis\JsonSchema\Errors\ValidationError;

interface Keyword
{
    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError;
}