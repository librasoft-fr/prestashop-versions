<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    Helper,
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class TypeKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $type;

    
    public function __construct($type)
    {
        $this->type = $type;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();
        if ($type && Helper::jsonTypeMatches($type, $this->type)) {
            return null;
        }

        return $this->error($schema, $context, 'type', 'The data ({type}) must match the type: {expected}', [
            'expected' => $this->type,
            'type' => $type,
        ]);
    }
}