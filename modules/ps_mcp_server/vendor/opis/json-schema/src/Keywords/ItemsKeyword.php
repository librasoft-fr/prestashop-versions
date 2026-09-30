<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class ItemsKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    
    protected $value;

    protected int $count = -1;
    protected bool $alwaysValid;
    protected string $keyword;
    protected int $startIndex;

    
    public function __construct($value, bool $alwaysValid = false, string $keyword = 'items', int $startIndex = 0)
    {
        $this->value = $value;
        $this->alwaysValid = $alwaysValid;

        if (is_array($value)) {
            $this->count = count($value);
        }

        $this->keyword = $keyword;
        $this->startIndex = $startIndex;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->alwaysValid || $this->value === true) {
            if ($this->count === -1) {
                $context->markAllAsEvaluatedItems();
            } else {
                $context->markCountAsEvaluatedItems($this->count);
            }
            return null;
        }

        $count = count($context->currentData());

        if ($this->startIndex >= $count) {
            
            return null;
        }

        if ($this->value === false) {
            if ($count === 0) {
                return null;
            }

            return $this->error($schema, $context, $this->keyword, 'Array must be empty');
        }

        if ($this->count >= 0) {

            $errors = $this->errorContainer($context->maxErrors());
            $max = min($count, $this->count);
            $evaluated = [];

            for ($i = $this->startIndex; $i < $max; $i++) {
                if ($this->value[$i] === true) {
                    $evaluated[] = $i;
                    continue;
                }

                if ($this->value[$i] === false) {
                    $context->addEvaluatedItems($evaluated);
                    return $this->error($schema, $context, $this->keyword, "Array item at index {index} is not allowed", [
                        'index' => $i,
                    ]);
                }

                if (is_object($this->value[$i]) && !($this->value[$i] instanceof Schema)) {
                    $this->value[$i] = $context->loader()->loadObjectSchema($this->value[$i]);
                }

                $context->pushDataPath($i);
                $error = $this->value[$i]->validate($context);
                $context->popDataPath();

                if ($error) {
                    $errors->add($error);
                    if ($errors->isFull()) {
                        break;
                    }
                } else {
                    $evaluated[] = $i;
                }
            }

            $context->addEvaluatedItems($evaluated);

            if ($errors->isEmpty()) {
                return null;
            }

            return $this->error($schema, $context, $this->keyword, 'Array items must match corresponding schemas', [],
                $errors);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $this->indexes($this->startIndex, $count),
            $this->keyword, 'All array items must match schema', [], $object);

        if ($object && $object->count()) {
            $context->addEvaluatedItems($object->getArrayCopy());
        }

        return $error;
    }

    
    protected function indexes(int $start, int $max): iterable
    {
        for ($i = $start; $i < $max; $i++) {
            yield $i;
        }
    }
}