<?php


namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Pragmas\GlobalsPragma;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser, VariablesTrait};

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