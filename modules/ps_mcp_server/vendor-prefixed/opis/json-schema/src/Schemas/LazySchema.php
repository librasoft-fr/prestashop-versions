<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Schemas;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

final class LazySchema extends AbstractSchema
{
    private SchemaParser $parser;

    private ?Schema $schema = null;

    public function __construct(SchemaInfo $info, SchemaParser $parser)
    {
        parent::__construct($info);
        $this->parser = $parser;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        return $this->schema()->validate($context);
    }

    public function schema(): Schema
    {
        if ($this->schema === null) {
            $this->schema = $this->parser->parseSchema($this->info);
        }

        return $this->schema;
    }
}
