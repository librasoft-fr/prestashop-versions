<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\KeywordValidator;

abstract class KeywordValidatorParser
{
    use KeywordParserTrait;

    abstract public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator;
}
