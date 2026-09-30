<?php


namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{ValidationContext, Filter, Schema};

class FilterExistsFilter implements Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $filter = $args['filter'] ?? $context->currentData();
        if (!is_string($filter)) {
            return false;
        }

        $type = null;
        if (isset($args['type'])) {
            if (!is_string($args['type'])) {
                return false;
            }
            $type = $args['type'];
        }

        $resolver = $context->loader()->parser()->getFilterResolver();

        if (!$resolver) {
            return false;
        }

        if ($type === null) {
            return (bool)$resolver->resolveAll($filter);
        }

        return (bool)$resolver->resolve($filter, $type);
    }
}