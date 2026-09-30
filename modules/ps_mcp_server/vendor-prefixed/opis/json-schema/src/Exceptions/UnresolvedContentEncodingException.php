<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Exceptions;

use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
