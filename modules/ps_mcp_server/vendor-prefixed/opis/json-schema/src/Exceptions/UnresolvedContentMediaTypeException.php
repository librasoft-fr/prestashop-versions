<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Exceptions;

use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
