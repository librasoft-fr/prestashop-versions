<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class PropertyNamesKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $value;

    
    public function __construct($value)
    {
        $this->value = $value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === true) {
            return null;
        }

        $props = $context->getObjectProperties();
        if (!$props) {
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'propertyNames', "No properties are allowed");
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        foreach ($props as $prop) {
            if ($error = $this->value->validate($context->newInstance($prop, $schema))) {
                return $this->error($schema, $context, 'propertyNames', "Property '{property}' must match schema", [
                    'property' => $prop,
                ], $error);
            }
        }

        return null;
    }
}