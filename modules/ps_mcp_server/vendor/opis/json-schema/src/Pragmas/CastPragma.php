<?php


namespace Opis\JsonSchema\Pragmas;

use Opis\JsonSchema\{Helper, ValidationContext, Pragma};

class CastPragma implements Pragma
{

    protected string $cast;

    
    protected $func;

    
    public function __construct(string $cast)
    {
        $this->cast = $cast;
        $this->func = $this->getCastFunction($cast);
    }

    
    public function enter(ValidationContext $context)
    {
        $currentType = $context->currentDataType();
        if ($currentType !== null && Helper::jsonTypeMatches($currentType, $this->cast)) {
            
            return $this;
        }
        unset($currentType);

        $currentData = $context->currentData();

        $context->setCurrentData(($this->func)($currentData));

        return $currentData;
    }

    
    public function leave(ValidationContext $context, $data): void
    {
        if ($data !== $this) {
            $context->setCurrentData($data);
        }
    }

    
    protected function getCastFunction(string $type): callable
    {
        $f = 'toNull';

        switch ($type) {
            case 'integer':
                $f = 'toInteger';
                break;
            case 'number':
                $f = 'toNumber';
                break;
            case 'string':
                $f = 'toString';
                break;
            case 'array':
                $f = 'toArray';
                break;
            case 'object':
                $f = 'toObject';
                break;
            case 'boolean':
                $f = 'toBoolean';
                break;
        }

        return [$this, $f];
    }

    
    public function toInteger($value): ?int
    {
        if ($value === null) {
            return 0;
        }

        return is_scalar($value) ? intval($value) : null;
    }

    
    public function toNumber($value): ?float
    {
        if ($value === null) {
            return 0.0;
        }

        return is_scalar($value) ? floatval($value) : null;
    }

    
    public function toString($value): ?string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    
    public function toArray($value): ?array
    {
        if ($value === null) {
            return [];
        }

        if (is_scalar($value)) {
            return [$value];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if (is_object($value)) {
            return array_values(get_object_vars($value));
        }

        return null;
    }

    
    public function toObject($value): ?object
    {
        if (is_object($value) || is_array($value)) {
            return (object) $value;
        }

        return null;
    }

    
    public function toBoolean($value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return !($value === '');
        }
        if (is_object($value)) {
            return count(get_object_vars($value)) > 0;
        }
        return boolval($value);
    }
}