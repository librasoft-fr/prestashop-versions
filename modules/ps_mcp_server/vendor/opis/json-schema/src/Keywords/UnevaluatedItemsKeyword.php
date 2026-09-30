<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Keyword, Schema, ValidationContext};

class UnevaluatedItemsKeyword implements Keyword
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
        $unevaluated = $context->getUnevaluatedItems();

        if (!$unevaluated) {
            return null;
        }

        if ($this->value === true) {
            $context->addEvaluatedItems($unevaluated);
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'unevaluatedItems',
                'Unevaluated array items are not allowed: {indexes}', [
                    'indexes' => $unevaluated,
                ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $unevaluated,
            'unevaluatedItems', 'All unevaluated array items must match schema: {indexes}', [
                'indexes' => $unevaluated,
            ], $object);

        if ($object && $object->count()) {
            $context->addEvaluatedItems($object->getArrayCopy());
        }

        return $error;
    }
}