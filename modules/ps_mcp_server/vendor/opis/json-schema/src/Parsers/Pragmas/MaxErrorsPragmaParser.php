<?php


namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Pragmas\MaxErrorsPragma;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser};

class MaxErrorsPragmaParser extends PragmaParser
{
    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_int($value)) {
            throw $this->pragmaException('Pragma {pragma} must be an integer', $info);
        }

        return new MaxErrorsPragma($value);
    }
}