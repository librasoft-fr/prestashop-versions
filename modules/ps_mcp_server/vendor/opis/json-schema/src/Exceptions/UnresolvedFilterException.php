<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedFilterException extends UnresolvedException
{

    protected string $filter;

    protected string $type;

    
    public function __construct(string $filter, string $type, Schema $schema, ValidationContext $context)
    {
        parent::__construct("Cannot resolve filter '{$filter}' for type '{$type}'", $schema, $context);
        $this->filter = $filter;
        $this->type = $type;
    }

    
    public function getFilter(): string
    {
        return $this->filter;
    }

    
    public function getType(): string
    {
        return $this->type;
    }
}