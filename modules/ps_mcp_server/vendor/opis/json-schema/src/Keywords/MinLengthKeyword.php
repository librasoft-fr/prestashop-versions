<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MinLengthKeyword implements Keyword
{
    use ErrorTrait;

    protected int $length;

    
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->length === 0) {
            return null;
        }

        $length = $context->getStringLength();

        if ($length >= $this->length) {
            return null;
        }

        return $this->error($schema, $context, 'minLength', "Minimum string length is {min}, found {length}", [
            'min' => $this->length,
            'length' => $length,
        ]);
    }
}