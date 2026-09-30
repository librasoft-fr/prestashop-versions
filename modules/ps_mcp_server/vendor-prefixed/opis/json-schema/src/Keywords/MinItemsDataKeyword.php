<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\JsonPointer;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class MinItemsDataKeyword extends MinItemsKeyword
{
    protected JsonPointer $value;

    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct(0);
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = $this->value->data($context->rootData(), $context->currentDataPath(), $this);

        if ($count === $this || !is_int($count) || $count < 0) {
            return $this->error($schema, $context, 'minItems', 'Invalid $data', [
                'pointer' => (string) $this->value,
            ]);
        }

        $this->count = $count;

        return parent::validate($context, $schema);
    }
}
