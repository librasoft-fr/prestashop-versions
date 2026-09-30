<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Keyword, Schema, ValidationContext};

class UnevaluatedPropertiesKeyword implements Keyword
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
        $unevaluated = $context->getUnevaluatedProperties();

        if (!$unevaluated) {
            return null;
        }

        if ($this->value === true) {
            $context->addEvaluatedProperties($unevaluated);
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'unevaluatedProperties',
                'Unevaluated object properties not allowed: {properties}', [
                    'properties' => $unevaluated,
                ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $unevaluated,
            'unevaluatedProperties', 'All unevaluated object properties must match schema: {properties}', [
                'properties' => $unevaluated,
            ], $object);


        if ($object && $object->count()) {
            $context->addEvaluatedProperties($object->getArrayCopy());
        }

        return $error;
    }
}