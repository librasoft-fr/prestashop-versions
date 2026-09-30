<?php


namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedContentMediaTypeException extends UnresolvedException
{
    protected string $media;

    
    public function __construct(string $media, Schema $schema, ValidationContext $context)
    {
        parent::__construct("Cannot resolve '{$media}' content media type", $schema, $context);
        $this->media = $media;
    }

    
    public function getContentMediaType(): string
    {
        return $this->media;
    }
}