<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class ContainsKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $value;
    protected ?int $min = null;
    protected ?int $max = null;

    
    public function __construct($value, ?int $min = null, ?int $max = null)
    {
        $this->value = $value;
        $this->min = $min;
        $this->max = $max;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $count = count($data);

        $context->markAllAsEvaluatedItems();

        if ($this->min > $count) {
            return $this->error($schema, $context, 'minContains', 'Array must have at least {min} items', [
                'min' => $this->min,
                'count' => $count,
            ]);
        }

        $isMaxNull = $this->max === null;

        if ($this->value === true) {
            if ($count) {
                if (!$isMaxNull && $count > $this->max) {
                    return $this->error($schema, $context, 'maxContains', 'Array must have at most {max} items', [
                        'max' => $this->max,
                        'count' => $count,
                    ]);
                }
                return null;
            }

            return $this->error($schema, $context, 'contains', 'Array must not be empty');
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'contains', 'Any array is invalid');
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $errors = [];
        $valid = 0;

        $isMinNull = $this->min === null;

        if ($isMaxNull && $isMinNull) {
            foreach ($data as $key => $item) {
                $context->pushDataPath($key);
                $error = $this->value->validate($context);
                $context->popDataPath();
                if ($error) {
                    $errors[] = $error;
                } else {
                    return null;
                }
            }

            return $this->error($schema, $context, 'contains', 'At least one array item must match schema', [],
                $errors);
        }

        foreach ($data as $key => $item) {
            $context->pushDataPath($key);
            $error = $this->value->validate($context);
            $context->popDataPath();

            if ($error) {
                $errors[] = $error;
            } else {
                $valid++;
            }
        }

        if (!$isMinNull && $valid < $this->min) {
            return $this->error($schema, $context, 'minContains', 'At least {min} array items must match schema', [
                'min' => $this->min,
                'count' => $valid,
            ]);
        }

        if (!$isMaxNull && $valid > $this->max) {
            return $this->error($schema, $context, 'maxContains', 'At most {max} array items must match schema', [
                'max' => $this->max,
                'count' => $valid,
            ]);
        }

        if ($valid) {
            return null;
        }

        return $this->error($schema, $context, 'contains', 'At least one array item must match schema', [],
            $errors);
    }
}