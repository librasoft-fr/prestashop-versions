<?php


namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{ValidationContext, Filter, Schema, JsonPointer};

class DataExistsFilter implements Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $ref = $args['ref'] ?? $context->currentData();
        if (!is_string($ref)) {
            return false;
        }

        $ref = JsonPointer::parse($ref);
        if ($ref === null) {
            return false;
        }

        return $ref->data($context->rootData(), $context->currentDataPath(), $this) !== $this;
    }
}