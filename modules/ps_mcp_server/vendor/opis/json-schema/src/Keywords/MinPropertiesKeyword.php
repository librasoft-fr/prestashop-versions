<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MinPropertiesKeyword implements Keyword
{
    use ErrorTrait;

    protected int $count;

    
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = count($context->getObjectProperties());

        if ($this->count <= $count) {
            return null;
        }

        return $this->error($schema, $context, 'minProperties',
            "Object must have at least {min} properties, {count} found", [
                'min' => $this->count,
                'count' => $count,
            ]);
    }
}