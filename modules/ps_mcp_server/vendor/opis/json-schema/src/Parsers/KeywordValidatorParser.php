<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;

abstract class KeywordValidatorParser
{
    use KeywordParserTrait;

    
    abstract public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator;
}