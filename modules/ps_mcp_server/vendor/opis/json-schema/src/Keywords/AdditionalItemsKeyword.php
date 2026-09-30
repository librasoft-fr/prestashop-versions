<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class AdditionalItemsKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    
    protected $value;

    protected int $index;

    
    public function __construct($value, int $startIndex)
    {
        $this->value = $value;
        $this->index = $startIndex;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === true) {
            $context->markAllAsEvaluatedItems();
            return null;
        }

        $data = $context->currentData();
        $count = count($data);

        if ($this->index >= $count) {
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'additionalItems', 'Array should not have additional items', [
                'index' => $this->index,
            ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $this->indexes($this->index, $count),
            'additionalItems', 'All additional array items must match schema', [], $object);

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