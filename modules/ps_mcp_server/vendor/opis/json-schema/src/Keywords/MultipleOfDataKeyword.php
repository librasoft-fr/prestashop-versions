<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class MultipleOfDataKeyword extends MultipleOfKeyword
{

    protected JsonPointer $value;

    
    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct(0);
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        
        $number = $this->value->data($context->rootData(), $context->currentDataPath(), $this);

        if ($number === $this || !(is_float($number) || is_int($number)) || $number <= 0) {
            return $this->error($schema, $context, 'multipleOf', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->number = $number;

        return parent::validate($context, $schema);
    }
}