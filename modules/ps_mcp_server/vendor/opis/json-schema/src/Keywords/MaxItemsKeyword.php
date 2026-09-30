<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MaxItemsKeyword implements Keyword
{
    use ErrorTrait;

    protected int $count;

    
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = count($context->currentData());

        if ($count <= $this->count) {
            return null;
        }

        return $this->error($schema, $context, "maxItems",
            "Array should have at most {max} items, {count} found", [
                'max' => $this->count,
                'count' => $count,
            ]);
    }
}