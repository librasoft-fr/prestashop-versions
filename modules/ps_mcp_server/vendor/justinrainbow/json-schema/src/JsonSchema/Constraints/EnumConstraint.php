<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Tool\DeepComparer;


class EnumConstraint extends Constraint
{
    
    public function check(&$element, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        
        if ($element instanceof UndefinedConstraint && (!isset($schema->required) || !$schema->required)) {
            return;
        }
        $type = gettype($element);

        foreach ($schema->enum as $enum) {
            $enumType = gettype($enum);

            if ($enumType === 'object'
                && $type === 'array'
                && $this->factory->getConfig(self::CHECK_MODE_TYPE_CAST)
                && DeepComparer::isEqual((object) $element, $enum)
            ) {
                return;
            }

            if (($type === $enumType) && DeepComparer::isEqual($element, $enum)) {
                return;
            }

            if (is_numeric($element) && is_numeric($enum) && DeepComparer::isEqual((float) $element, (float) $enum)) {
                return;
            }
        }

        $this->addError(ConstraintError::ENUM(), $path, ['enum' => $schema->enum]);
    }
}
