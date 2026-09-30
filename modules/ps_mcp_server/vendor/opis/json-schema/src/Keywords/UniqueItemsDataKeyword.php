<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

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
                'pointer' => (string)$this->value,
            ]);
        }

        return $value ? parent::validate($context, $schema) : null;
    }
}