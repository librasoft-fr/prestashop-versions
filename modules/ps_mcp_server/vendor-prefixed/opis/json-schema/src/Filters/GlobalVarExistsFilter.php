<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Filters;

use PsMcpServerDeps\Opis\JsonSchema\Filter;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class GlobalVarExistsFilter implements Filter
{
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $var = $args['var'] ?? $context->currentData();

        if (!is_string($var)) {
            return false;
        }

        $globals = $context->globals();

        if (!array_key_exists($var, $globals)) {
            return false;
        }

        if (array_key_exists('value', $args)) {
            return $globals[$var] == $args['value'];
        }

        return true;
    }
}
