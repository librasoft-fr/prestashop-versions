<?php

declare(strict_types=1);

namespace JsonSchema;

interface SchemaStorageInterface
{
    
    public function addSchema(string $id, $schema = null): void;

    
    public function getSchema(string $id);

    
    public function resolveRef(string $ref);

    
    public function resolveRefSchema($refSchema);
}
