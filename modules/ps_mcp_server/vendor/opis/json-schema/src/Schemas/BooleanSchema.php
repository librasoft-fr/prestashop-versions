<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Info\{DataInfo, SchemaInfo};
use Opis\JsonSchema\Errors\ValidationError;

final class BooleanSchema extends AbstractSchema
{

    private bool $data;

    
    public function __construct(SchemaInfo $info)
    {
        parent::__construct($info);
        $this->data = $info->data();
    }

    
    public function validate(ValidationContext $context): ?ValidationError
    {
        if ($this->data) {
            return null;
        }

        return new ValidationError('', $this, DataInfo::fromContext($context), 'Data not allowed');
    }
}