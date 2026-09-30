<?php


namespace Opis\JsonSchema\Exceptions;

use RuntimeException;
use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedException extends RuntimeException implements SchemaException
{

    protected Schema $schema;

    protected ValidationContext $context;

    
    public function __construct(string $message, Schema $schema, ValidationContext $context)
    {
        parent::__construct($message);
        $this->schema = $schema;
        $this->context = $context;
    }

    
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    
    public function getContext(): ValidationContext
    {
        return $this->context;
    }
}