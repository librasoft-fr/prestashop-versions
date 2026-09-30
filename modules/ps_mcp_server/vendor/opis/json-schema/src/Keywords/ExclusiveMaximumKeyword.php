<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class ExclusiveMaximumKeyword implements Keyword
{
    use ErrorTrait;

    protected float $number;

    
    public function __construct(float $number)
    {
        $this->number = $number;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($context->currentData() < $this->number) {
            return null;
        }

        return $this->error($schema, $context, 'exclusiveMaximum', "Number must be lower than {max}", [
            'max' => $this->number,
        ]);
    }
}