<?php


namespace Opis\JsonSchema\Exceptions;

use RuntimeException;
use Opis\JsonSchema\Info\SchemaInfo;

class ParseException extends RuntimeException implements SchemaException
{

    protected ?SchemaInfo $info = null;

    
    public function __construct(string $message, ?SchemaInfo $info = null)
    {
        parent::__construct($message, 0);
        $this->info = $info;
    }

    
    public function schemaInfo(): ?SchemaInfo
    {
        return $this->info;
    }
}