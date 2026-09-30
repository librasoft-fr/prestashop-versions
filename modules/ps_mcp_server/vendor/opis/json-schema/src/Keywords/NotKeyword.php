<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class NotKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $value;

    
    public function __construct($value)
    {
        $this->value = $value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === false) {
            return null;
        }
        if ($this->value === true) {
            return $this->error($schema, $context, 'not', "The data is never valid");
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $error = $context->validateSchemaWithoutEvaluated($this->value, 1);

        if ($error) {
            return null;
        }

        return $this->error($schema, $context, 'not', 'The data must not match schema');
    }
}