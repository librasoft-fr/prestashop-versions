<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Filters;

use PsMcpServerDeps\Opis\JsonSchema\Filter;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
