<?php


namespace Opis\JsonSchema\Exceptions;

use RuntimeException;
use Opis\JsonSchema\Uri;

class DuplicateSchemaIdException extends RuntimeException implements SchemaException
{

    protected Uri $id;

    protected ?object $data = null;

    
    public function __construct(Uri $id, ?object $data = null)
    {
        parent::__construct("Duplicate schema id: {$id}", 0);
        $this->id = $id;
        $this->data = $data;
    }

    
    public function getData(): ?object
    {
        return $this->data;
    }

    
    public function getId(): Uri
    {
        return $this->id;
    }
}