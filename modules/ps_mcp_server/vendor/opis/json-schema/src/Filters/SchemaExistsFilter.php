<?php


namespace Opis\JsonSchema\Filters;

use Opis\Uri\UriTemplate;
use Opis\JsonSchema\{ValidationContext, Filter, Schema, Uri};
use Opis\JsonSchema\Variables\VariablesContainer;

class SchemaExistsFilter implements Filter
{
    
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $ref = $args['ref'] ?? $context->currentData();
        if (!is_string($ref)) {
            return false;
        }

        if (UriTemplate::isTemplate($ref)) {
            if (isset($args['vars']) && is_object($args['vars'])) {
                $vars = new VariablesContainer($args['vars'], false);
                $vars = $vars->resolve($context->rootData(), $context->currentDataPath());
                if (!is_array($vars)) {
                    $vars = (array)$vars;
                }
                $vars += $context->globals();
            } else {
                $vars = $context->globals();
            }

            $ref = (new UriTemplate($ref))->resolve($vars);

            unset($vars);
        }

        unset($args);

        return $this->refExists($ref, $context, $schema);
    }

    
    protected function refExists(string $ref, ValidationContext $context, Schema $schema): bool
    {
        if ($ref === '') {
            return false;
        }

        if ($ref === '#') {
            return true;
        }

        $info = $schema->info();

        $id = Uri::merge($ref, $info->idBaseRoot(), true);

        if ($id === null) {
            return false;
        }

        return $context->loader()->loadSchemaById($id) !== null;
    }
}