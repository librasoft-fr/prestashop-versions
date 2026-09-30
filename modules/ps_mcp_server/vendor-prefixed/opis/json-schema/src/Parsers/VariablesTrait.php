<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Variables;
use PsMcpServerDeps\Opis\JsonSchema\Variables\VariablesContainer;

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
