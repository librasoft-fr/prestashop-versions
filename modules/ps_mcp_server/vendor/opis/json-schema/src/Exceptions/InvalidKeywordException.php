<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\Info\SchemaInfo;

class InvalidKeywordException extends ParseException
{

    protected string $keyword;

    
    public function __construct(string $message, string $keyword, ?SchemaInfo $info = null)
    {
        parent::__construct($message, $info);
        $this->keyword = $keyword;
    }

    
    public function keyword(): string
    {
        return $this->keyword;
    }
}