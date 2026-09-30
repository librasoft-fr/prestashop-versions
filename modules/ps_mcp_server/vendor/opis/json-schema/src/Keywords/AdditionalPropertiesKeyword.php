<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class AdditionalPropertiesKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    
    protected $value;

    
    public function __construct($value)
    {
        $this->value = $value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === true) {
            $context->markAllAsEvaluatedProperties();
            return null;
        }

        $props = $context->getUncheckedProperties();

        if (!$props) {
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context,
                'additionalProperties', 'Additional object properties are not allowed: {properties}', [
                    'properties' => $props
                ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $props,
            'additionalProperties', 'All additional object properties must match schema: {properties}', [
                'properties' => $props
            ], $object);

        if ($object && $object->count()) {
            $context->addEvaluatedProperties($object->getArrayCopy());
        }

        return $error;
    }
}