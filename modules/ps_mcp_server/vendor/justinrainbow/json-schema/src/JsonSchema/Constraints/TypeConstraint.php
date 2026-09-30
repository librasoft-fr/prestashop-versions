<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidArgumentException;
use UnexpectedValueException as StandardUnexpectedValueException;


class TypeConstraint extends Constraint
{
    
    public static $wording = [
        'integer' => 'an integer',
        'number'  => 'a number',
        'boolean' => 'a boolean',
        'object'  => 'an object',
        'array'   => 'an array',
        'string'  => 'a string',
        'null'    => 'a null',
        'any'     => null, 
        0         => null, 
    ];

    
    public function check(&$value = null, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $type = isset($schema->type) ? $schema->type : null;
        $isValid = false;
        $coerce = $this->factory->getConfig(self::CHECK_MODE_COERCE_TYPES);
        $earlyCoerce = $this->factory->getConfig(self::CHECK_MODE_EARLY_COERCE);
        $wording = [];

        if (is_array($type)) {
            $this->validateTypesArray($value, $type, $wording, $isValid, $path, $coerce && $earlyCoerce);
            if (!$isValid && $coerce && !$earlyCoerce) {
                $this->validateTypesArray($value, $type, $wording, $isValid, $path, true);
            }
        } elseif (is_object($type)) {
            $this->checkUndefined($value, $type, $path);

            return;
        } else {
            $isValid = $this->validateType($value, $type, $coerce && $earlyCoerce);
            if (!$isValid && $coerce && !$earlyCoerce) {
                $isValid = $this->validateType($value, $type, true);
            }
        }

        if ($isValid === false) {
            if (!is_array($type)) {
                $this->validateTypeNameWording($type);
                $wording[] = self::$wording[$type];
            }
            $this->addError(ConstraintError::TYPE(), $path, [
                    'found' => gettype($value),
                    'expected' => $this->implodeWith($wording, ', ', 'or')
            ]);
        }
    }

    
    protected function validateTypesArray(&$value, array $type, &$validTypesWording, &$isValid, $path, $coerce = false)
    {
        foreach ($type as $tp) {
            
            if ($isValid) {
                return;
            }

            
            
            if (is_object($tp)) {
                if (!$isValid) {
                    $validator = $this->factory->createInstanceFor('type');
                    $subSchema = new \stdClass();
                    $subSchema->type = $tp;
                    $validator->check($value, $subSchema, $path, null);
                    $error = $validator->getErrors();
                    $isValid = !(bool) $error;
                    $validTypesWording[] = self::$wording['object'];
                }
            } else {
                $this->validateTypeNameWording($tp);
                $validTypesWording[] = self::$wording[$tp];
                if (!$isValid) {
                    $isValid = $this->validateType($value, $tp, $coerce);
                }
            }
        }
    }

    
    protected function implodeWith(array $elements, $delimiter = ', ', $listEnd = false)
    {
        if ($listEnd === false || !isset($elements[1])) {
            return implode($delimiter, $elements);
        }
        $lastElement  = array_slice($elements, -1);
        $firsElements = join($delimiter, array_slice($elements, 0, -1));
        $implodedElements = array_merge([$firsElements], $lastElement);

        return join(" $listEnd ", $implodedElements);
    }

    
    protected function validateTypeNameWording($type)
    {
        if (!array_key_exists($type, self::$wording)) {
            throw new StandardUnexpectedValueException(
                sprintf(
                    'No wording for %s available, expected wordings are: [%s]',
                    var_export($type, true),
                    implode(', ', array_filter(self::$wording)))
            );
        }
    }

    
    protected function validateType(&$value, $type, $coerce = false)
    {
        
        if (!$type) {
            return true;
        }

        if ('any' === $type) {
            return true;
        }

        if ('object' === $type) {
            return $this->getTypeCheck()->isObject($value);
        }

        if ('array' === $type) {
            if ($coerce) {
                $value = $this->toArray($value);
            }

            return $this->getTypeCheck()->isArray($value);
        }

        if ('integer' === $type) {
            if ($coerce) {
                $value = $this->toInteger($value);
            }

            return is_int($value);
        }

        if ('number' === $type) {
            if ($coerce) {
                $value = $this->toNumber($value);
            }

            return is_numeric($value) && !is_string($value);
        }

        if ('boolean' === $type) {
            if ($coerce) {
                $value = $this->toBoolean($value);
            }

            return is_bool($value);
        }

        if ('string' === $type) {
            if ($coerce) {
                $value = $this->toString($value);
            }

            return is_string($value);
        }

        if ('null' === $type) {
            if ($coerce) {
                $value = $this->toNull($value);
            }

            return is_null($value);
        }

        throw new InvalidArgumentException((is_object($value) ? 'object' : $value) . ' is an invalid type for ' . $type);
    }

    
    protected function toBoolean($value)
    {
        if ($value === 1 || $value === 'true') {
            return true;
        }
        if (is_null($value) || $value === 0 || $value === 'false') {
            return false;
        }
        if ($this->getTypeCheck()->isArray($value) && count($value) === 1) {
            return $this->toBoolean(reset($value));
        }

        return $value;
    }

    
    protected function toNumber($value)
    {
        if (is_numeric($value)) {
            return $value + 0; 
        }
        if (is_bool($value) || is_null($value)) {
            return (int) $value;
        }
        if ($this->getTypeCheck()->isArray($value) && count($value) === 1) {
            return $this->toNumber(reset($value));
        }

        return $value;
    }

    
    protected function toInteger($value)
    {
        $numberValue = $this->toNumber($value);
        if (is_numeric($numberValue) && (int) $numberValue == $numberValue) {
            return (int) $numberValue; 
        }

        return $value;
    }

    
    protected function toArray($value)
    {
        if (is_scalar($value) || is_null($value)) {
            return [$value];
        }

        return $value;
    }

    
    protected function toString($value)
    {
        if (is_numeric($value)) {
            return "$value";
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_null($value)) {
            return '';
        }
        if ($this->getTypeCheck()->isArray($value) && count($value) === 1) {
            return $this->toString(reset($value));
        }

        return $value;
    }

    
    protected function toNull($value)
    {
        if ($value === 0 || $value === false || $value === '') {
            return null;
        }
        if ($this->getTypeCheck()->isArray($value) && count($value) === 1) {
            return $this->toNull(reset($value));
        }

        return $value;
    }
}
