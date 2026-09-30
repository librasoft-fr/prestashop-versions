<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedReferenceException extends UnresolvedException
{

    protected string $ref;

    
    public function __construct(string $ref, Schema $schema, ValidationContext $context)
    {
        parent::__construct("Unresolved reference: {$ref}", $schema, $context);
        $this->ref = $ref;
    }

    
    public function getRef(): string
    {
        return $this->ref;
    }
}