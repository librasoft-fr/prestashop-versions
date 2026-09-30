<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Tool\DeepComparer;


class CollectionConstraint extends Constraint
{
    
    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        
        if (isset($schema->minItems) && count($value) < $schema->minItems) {
            $this->addError(ConstraintError::MIN_ITEMS(), $path, ['minItems' => $schema->minItems, 'found' => count($value)]);
        }

        
        if (isset($schema->maxItems) && count($value) > $schema->maxItems) {
            $this->addError(ConstraintError::MAX_ITEMS(), $path, ['maxItems' => $schema->maxItems, 'found' => count($value)]);
        }

        
        if (isset($schema->uniqueItems) && $schema->uniqueItems) {
            $count = count($value);
            for ($x = 0; $x < $count - 1; $x++) {
                for ($y = $x + 1; $y < $count; $y++) {
                    if (DeepComparer::isEqual($value[$x], $value[$y])) {
                        $this->addError(ConstraintError::UNIQUE_ITEMS(), $path);
                        break 2;
                    }
                }
            }
        }

        $this->validateItems($value, $schema, $path, $i);
    }

    
    protected function validateItems(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (\is_null($schema) || !isset($schema->items)) {
            return;
        }

        if ($schema->items === true) {
            return;
        }

        if (is_object($schema->items)) {
            
            foreach ($value as $k => &$v) {
                $initErrors = $this->getErrors();

                
                $this->checkUndefined($v, $schema->items, $path, $k);

                
                if (count($initErrors) < count($this->getErrors()) && (isset($schema->additionalItems) && $schema->additionalItems !== false)) {
                    $secondErrors = $this->getErrors();
                    $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                }

                
                if (isset($secondErrors) && count($secondErrors) < count($this->getErrors())) {
                    $this->errors = $secondErrors;
                } elseif (isset($secondErrors) && count($secondErrors) === count($this->getErrors())) {
                    $this->errors = $initErrors;
                }
            }
            unset($v); 
        } else {
            
            foreach ($value as $k => &$v) {
                if (array_key_exists($k, $schema->items)) {
                    $this->checkUndefined($v, $schema->items[$k], $path, $k);
                } else {
                    
                    if (property_exists($schema, 'additionalItems')) {
                        if ($schema->additionalItems !== false) {
                            $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                        } else {
                            $this->addError(
                                ConstraintError::ADDITIONAL_ITEMS(),
                                $path,
                                [
                                    'item' => $i,
                                    'property' => $k,
                                    'additionalItems' => $schema->additionalItems
                                ]
                            );
                        }
                    } else {
                        
                        $this->checkUndefined($v, new \stdClass(), $path, $k);
                    }
                }
            }
            unset($v); 

            
            if (count($value) > 0) {
                for ($k = count($value); $k < count($schema->items); $k++) {
                    $undefinedInstance = $this->factory->createInstanceFor('undefined');
                    $this->checkUndefined($undefinedInstance, $schema->items[$k], $path, $k);
                }
            }
        }
    }
}
