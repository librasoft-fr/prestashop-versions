<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedContentEncodingException extends UnresolvedException
{
    protected string $encoding;

    
    public function __construct(string $encoding, Schema $schema, ValidationContext $context)
    {
        parent::__construct("Cannot resolve '{$encoding}' content encoding", $schema, $context);
        $this->encoding = $encoding;
    }

    
    public function getContentEncoding(): string
    {
        return $this->encoding;
    }
}