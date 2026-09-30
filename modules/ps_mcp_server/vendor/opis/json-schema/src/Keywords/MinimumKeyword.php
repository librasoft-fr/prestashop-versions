<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MinimumKeyword implements Keyword
{
    use ErrorTrait;

    protected float $number;

    
    public function __construct(float $number)
    {
        $this->number = $number;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($context->currentData() >= $this->number) {
            return null;
        }

        return $this->error($schema, $context, 'minimum', "Number must be greater than or equal to {min}", [
            'min' => $this->number,
        ]);
    }
}