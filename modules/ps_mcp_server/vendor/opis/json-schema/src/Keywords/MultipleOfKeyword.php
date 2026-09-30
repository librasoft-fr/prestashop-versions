<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema,
    Helper
};
use Opis\JsonSchema\Errors\ValidationError;

class MultipleOfKeyword implements Keyword
{
    use ErrorTrait;

    protected float $number;

    
    public function __construct(float $number)
    {
        $this->number = $number;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (Helper::isMultipleOf($context->currentData(), $this->number)) {
            return null;
        }

        return $this->error($schema, $context, 'multipleOf', "Number must be a multiple of {divisor}", [
            'divisor' => $this->number,
        ]);
    }
}