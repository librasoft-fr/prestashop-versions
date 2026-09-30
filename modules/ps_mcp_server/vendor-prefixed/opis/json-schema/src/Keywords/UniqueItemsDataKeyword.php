<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\JsonPointer;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class UniqueItemsDataKeyword extends UniqueItemsKeyword
{
    protected JsonPointer $value;

    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->value->data($context->rootData(), $context->currentDataPath(), $this);

        if ($value === $this || !is_bool($value)) {
            return $this->error($schema, $context, 'uniqueItems', 'Invalid $data', [
                'pointer' => (string) $this->value,
            ]);
        }

        return $value ? parent::validate($context, $schema) : null;
    }
}
