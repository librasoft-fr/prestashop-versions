<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MaxPropertiesKeywords implements Keyword
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

        if ($count <= $this->count) {
            return null;
        }

        return $this->error($schema, $context, 'maxProperties',
            "Object must have at most {max} properties, {count} found", [
                'max' => $this->count,
                'count' => $count,
            ]);
    }
}