<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\Info\SchemaInfo;

class InvalidPragmaException extends InvalidKeywordException
{

    protected string $pragma;

    
    public function __construct(string $message, string $pragma, ?SchemaInfo $info = null)
    {
        parent::__construct($message, '$pragma', $info);
        $this->pragma = $pragma;
    }

    
    public function pragma(): string
    {
        return $this->pragma;
    }
}