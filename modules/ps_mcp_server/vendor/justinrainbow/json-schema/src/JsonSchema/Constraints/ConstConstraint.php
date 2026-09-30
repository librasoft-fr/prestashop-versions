<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Tool\DeepComparer;


class ConstConstraint extends Constraint
{
    
    public function check(&$element, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        
        if ($element instanceof UndefinedConstraint && (!isset($schema->required) || !$schema->required)) {
            return;
        }
        $const = $schema->const;

        $type = gettype($element);
        $constType = gettype($const);

        if ($this->factory->getConfig(self::CHECK_MODE_TYPE_CAST) && $type === 'array' && $constType === 'object') {
            if (DeepComparer::isEqual((object) $element, $const)) {
                return;
            }
        }

        if (DeepComparer::isEqual($element, $const)) {
            return;
        }

        $this->addError(ConstraintError::CONSTANT(), $path, ['const' => $schema->const]);
    }
}
