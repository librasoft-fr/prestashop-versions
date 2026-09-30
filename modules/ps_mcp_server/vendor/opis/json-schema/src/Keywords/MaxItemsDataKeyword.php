<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class MaxItemsDataKeyword extends MaxItemsKeyword
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
            return $this->error($schema, $context, 'maxItems', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->count = $count;

        return parent::validate($context, $schema);
    }
}