<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Schemas;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Info\DataInfo;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
