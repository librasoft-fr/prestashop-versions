<?php


namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{ValidationContext, Filter, Schema};

class SlotExistsFilter implements Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $slot = $args['slot'] ?? $context->currentData();
        if (!is_string($slot)) {
            return false;
        }

        return $context->slot($slot) !== null;
    }
}