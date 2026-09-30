<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};

class ConstKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $const;

    
    public function __construct($const)
    {
        $this->const = $const;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (Helper::equals($this->const, $context->currentData())) {
            return null;
        }

        return $this->error($schema, $context, 'const', 'The data must match the const value', [
            'const' => $this->const
        ]);
    }
}
