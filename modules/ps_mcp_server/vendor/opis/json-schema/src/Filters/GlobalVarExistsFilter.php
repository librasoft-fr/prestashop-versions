<?php


namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{ValidationContext, Filter, Schema};

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