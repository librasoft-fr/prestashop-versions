<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    Filter,
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\{ValidationError, CustomError};
use Opis\JsonSchema\Exceptions\UnresolvedFilterException;

class FiltersKeyword implements Keyword
{
    use ErrorTrait;

    
    protected array $filters;

    
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();

        foreach ($this->filters as $filter) {
            if (!isset($filter->types[$type])) {
                throw new UnresolvedFilterException($filter->name, $type, $schema, $context);
            }

            $func = $filter->types[$type];

            if ($filter->args) {
                $args = (array)$filter->args->resolve($context->rootData(), $context->currentDataPath());
                $args += $context->globals();
            } else {
                $args = $context->globals();
            }

            try {
                if ($func instanceof Filter) {
                    $ok = $func->validate($context, $schema, $args);
                } else {
                    $ok = $func($context->currentData(), $args);
                }
            } catch (CustomError $error) {
                return $this->error($schema, $context, '$filters', $error->getMessage(), $error->getArgs() + [
                    'filter' => $filter->name,
                    'type' => $type,
                    'args' => $args,
                ]);
            }

            if ($ok) {
                unset($func, $args, $ok);
                continue;
            }

            return $this->error($schema, $context, '$filters', "Filter '{filter}' ({type}) was not passed", [
                'filter' => $filter->name,
                'type' => $type,
                'args' => $args,
            ]);
        }

        return null;
    }
}