<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class MinLengthDataKeyword extends MinLengthKeyword
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
            return $this->error($schema, $context, 'minLength', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->length = $length;

        return parent::validate($context, $schema);
    }
}