<?php


namespace Opis\JsonSchema;

interface Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool;
}