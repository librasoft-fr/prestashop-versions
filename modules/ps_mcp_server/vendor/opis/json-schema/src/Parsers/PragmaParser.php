<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Exceptions\InvalidPragmaException;
use Opis\JsonSchema\Pragma;

abstract class PragmaParser
{
    protected string $pragma;

    
    public function __construct(string $pragma)
    {
        $this->pragma = $pragma;
    }

    
    abstract public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma;

    
    protected function pragmaExists(object $schema, ?string $pragma = null): bool
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->isObject() ? $schema->data() : null;
        }

        return is_object($schema) && property_exists($schema, $pragma ?? $this->pragma);
    }

    
    protected function pragmaValue(object $schema, ?string $pragma = null)
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->isObject() ? $schema->data() : null;
        }

        return is_object($schema) ? $schema->{$pragma ?? $this->pragma} : null;
    }

    
    protected function pragmaException(string $message, SchemaInfo $info, ?string $pragma = null): InvalidPragmaException
    {
        $pragma = $pragma ?? $this->pragma;

        return new InvalidPragmaException(str_replace('{pragma}', $pragma, $message), $pragma, $info);
    }
}