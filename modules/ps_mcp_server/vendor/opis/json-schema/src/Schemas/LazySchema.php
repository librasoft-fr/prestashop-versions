<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\{ValidationContext, Info\SchemaInfo, Schema};
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Errors\ValidationError;

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