<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class MaxLengthKeyword implements Keyword
{
    use ErrorTrait;

    protected int $length;

    
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $length = $context->getStringLength();

        if ($length <= $this->length) {
            return null;
        }

        return $this->error($schema, $context, 'maxLength', "Maximum string length is {max}, found {length}",
            [
                'max' => $this->length,
                'length' => $length,
            ]);
    }
}