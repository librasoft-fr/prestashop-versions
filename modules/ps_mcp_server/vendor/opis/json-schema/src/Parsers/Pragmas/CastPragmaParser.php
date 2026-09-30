<?php


namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Pragmas\CastPragma;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser};

class CastPragmaParser extends PragmaParser
{
    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_string($value) || !Helper::isValidJsonType($value)) {
            throw $this->pragmaException('Pragma {pragma} must contain a valid json type name', $info);
        }

        return new CastPragma($value);
    }
}