<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\JsonPointer;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class MaxLengthDataKeyword extends MaxLengthKeyword
{
    protected JsonPointer $value;

    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct(0);
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $length = $this->value->data($context->rootData(), $context->currentDataPath(), $this);

        if ($length === $this || !is_int($length) || $length < 0) {
            return $this->error($schema, $context, 'maxLength', 'Invalid $data', [
                'pointer' => (string) $this->value,
            ]);
        }

        $this->length = $length;

        return parent::validate($context, $schema);
    }
}
