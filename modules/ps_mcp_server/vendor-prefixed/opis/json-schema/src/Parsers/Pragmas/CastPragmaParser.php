<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\PragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;
use PsMcpServerDeps\Opis\JsonSchema\Pragma;
use PsMcpServerDeps\Opis\JsonSchema\Pragmas\CastPragma;

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
