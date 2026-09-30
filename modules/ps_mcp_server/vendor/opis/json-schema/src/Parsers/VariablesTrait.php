<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Variables;
use Opis\JsonSchema\Variables\{VariablesContainer};

trait VariablesTrait
{
    
    protected function createVariables(SchemaParser $parser, $vars, bool $lazy = true): Variables
    {
        return new VariablesContainer(
            $vars,
            $lazy,
            $parser->option('varRefKey', '$ref'),
            $parser->option('varEachKey', '$each'),
            $parser->option('varDefaultKey', 'default')
        );
    }
}