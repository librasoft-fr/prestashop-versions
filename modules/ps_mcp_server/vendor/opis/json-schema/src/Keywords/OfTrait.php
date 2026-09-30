<?php


namespace Opis\JsonSchema\Keywords;

use ArrayObject;
use Opis\JsonSchema\ValidationContext;

trait OfTrait
{
    protected function createArrayObject(ValidationContext $context): ?ArrayObject
    {
        return $context->trackUnevaluated() ? new ArrayObject() : null;
    }

    protected function addEvaluatedFromArrayObject(?ArrayObject $object, ValidationContext $context): void
    {
        if (!$object || !$object->count()) {
            return;
        }

        foreach ($object as $value) {
            if (isset($value['properties'])) {
                $context->addEvaluatedProperties($value['properties']);
            }
            if (isset($value['items'])) {
                $context->addEvaluatedItems($value['items']);
            }
        }
    }
}