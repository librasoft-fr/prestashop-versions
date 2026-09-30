<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\PragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\VariablesTrait;
use PsMcpServerDeps\Opis\JsonSchema\Pragma;
use PsMcpServerDeps\Opis\JsonSchema\Pragmas\GlobalsPragma;

class GlobalsPragmaParser extends PragmaParser
{
    use VariablesTrait;

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$parser->option('allowGlobals') || !$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_object($value)) {
            throw $this->pragmaException('Pragma {pragma} must be an object', $info);
        }

        $value = get_object_vars($value);

        return $value ? new GlobalsPragma($this->createVariables($parser, $value)) : null;
    }
}
