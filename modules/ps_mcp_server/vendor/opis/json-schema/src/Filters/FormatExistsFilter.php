<?php


namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{ValidationContext, Filter, Schema};

class FormatExistsFilter implements Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $format = $args['format'] ?? $context->currentData();
        if (!is_string($format)) {
            return false;
        }

        $type = null;
        if (isset($args['type'])) {
            if (!is_string($args['type'])) {
                return false;
            }
            $type = $args['type'];
        }

        $resolver = $context->loader()->parser()->getFormatResolver();

        if (!$resolver) {
            return false;
        }

        if ($type === null) {
            return (bool)$resolver->resolveAll($format);
        }

        return (bool)$resolver->resolve($format, $type);
    }
}