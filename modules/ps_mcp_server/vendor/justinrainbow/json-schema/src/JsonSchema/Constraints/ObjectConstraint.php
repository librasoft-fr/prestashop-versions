<?php

declare(strict_types=1);

namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;

class ObjectConstraint extends Constraint
{
    
    protected $appliedDefaults = [];

    
    public function check(
        &$element,
        $schema = null,
        ?JsonPointer $path = null,
        $properties = null,
        $additionalProp = null,
        $patternProperties = null,
        $appliedDefaults = []
    ): void {
        if ($element instanceof UndefinedConstraint) {
            return;
        }

        $this->appliedDefaults = $appliedDefaults;

        $matches = [];
        if ($patternProperties) {
            
            $matches = $this->validatePatternProperties($element, $path, $patternProperties);
        }

        if ($properties) {
            
            $this->validateProperties($element, $properties, $path);
        }

        
        $this->validateElement($element, $matches, $schema, $path, $properties, $additionalProp);
    }

    public function validatePatternProperties($element, ?JsonPointer $path, $patternProperties)
    {
        $matches = [];
        foreach ($patternProperties as $pregex => $schema) {
            $fullRegex = self::jsonPatternToPhpRegex($pregex);

            
            if (@preg_match($fullRegex, '') === false) {
                $this->addError(ConstraintError::PREGEX_INVALID(), $path, ['pregex' => $pregex]);
                continue;
            }
            foreach ($element as $i => $value) {
                if (preg_match($fullRegex, (string) $i)) {
                    $matches[] = $i;
                    $this->checkUndefined($value, $schema ?: new \stdClass(), $path, $i, in_array($i, $this->appliedDefaults));
                }
            }
        }

        return $matches;
    }

    
    public function validateElement($element, $matches, $schema = null, ?JsonPointer $path = null,
        $properties = null, $additionalProp = null)
    {
        $this->validateMinMaxConstraint($element, $schema, $path);

        foreach ($element as $i => $value) {
            $definition = $this->getProperty($properties, $i);

            
            if (!in_array($i, $matches) && $additionalProp === false && $this->inlineSchemaProperty !== $i && !$definition) {
                $this->addError(ConstraintError::ADDITIONAL_PROPERTIES(), $path, ['property' => $i]);
            }

            
            if (!in_array($i, $matches) && $additionalProp && !$definition) {
                if ($additionalProp === true) {
                    $this->checkUndefined($value, null, $path, $i, in_array($i, $this->appliedDefaults));
                } else {
                    $this->checkUndefined($value, $additionalProp, $path, $i, in_array($i, $this->appliedDefaults));
                }
            }

            
            $require = $this->getProperty($definition, 'requires');
            if ($require && !$this->getProperty($element, $require)) {
                $this->addError(ConstraintError::REQUIRES(), $path, [
                    'property' => $i,
                    'requiredProperty' => $require
                ]);
            }

            $property = $this->getProperty($element, $i, $this->factory->createInstanceFor('undefined'));
            if (is_object($property)) {
                $this->validateMinMaxConstraint(!($property instanceof UndefinedConstraint) ? $property : $element, $definition, $path);
            }
        }
    }

    
    public function validateProperties(&$element, $properties = null, ?JsonPointer $path = null)
    {
        $undefinedConstraint = $this->factory->createInstanceFor('undefined');

        foreach ($properties as $i => $value) {
            $property = &$this->getProperty($element, $i, $undefinedConstraint);
            $definition = $this->getProperty($properties, $i);

            if (is_object($definition)) {
                
                $this->checkUndefined($property, $definition, $path, $i, in_array($i, $this->appliedDefaults));
            }
        }
    }

    
    protected function &getProperty(&$element, $property, $fallback = null)
    {
        if (is_array($element) && (isset($element[$property]) || array_key_exists($property, $element)) ) {
            return $element[$property];
        } elseif (is_object($element) && property_exists($element, (string) $property)) {
            return $element->$property;
        }

        return $fallback;
    }

    
    protected function validateMinMaxConstraint($element, $objectDefinition, ?JsonPointer $path = null)
    {
        if (!$this->getTypeCheck()::isObject($element)) {
            return;
        }

        
        if (isset($objectDefinition->minProperties) && is_int($objectDefinition->minProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) < max(0, $objectDefinition->minProperties)) {
                $this->addError(ConstraintError::PROPERTIES_MIN(), $path, ['minProperties' => $objectDefinition->minProperties]);
            }
        }
        
        if (isset($objectDefinition->maxProperties) && is_int($objectDefinition->maxProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) > max(0, $objectDefinition->maxProperties)) {
                $this->addError(ConstraintError::PROPERTIES_MAX(), $path, ['maxProperties' => $objectDefinition->maxProperties]);
            }
        }
    }
}
