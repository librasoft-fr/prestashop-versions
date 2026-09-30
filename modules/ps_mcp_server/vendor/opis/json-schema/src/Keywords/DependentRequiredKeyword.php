<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class DependentRequiredKeyword implements Keyword
{
    use ErrorTrait;

    
    protected array $value;

    
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();

        foreach ($this->value as $name => $value) {
            if (!property_exists($data, $name)) {
                continue;
            }
            foreach ($value as $prop) {
                if (!property_exists($data, $prop)) {
                    return $this->error($schema, $context, 'dependentRequired',
                        "'{$prop}' property is required by '{$name}' property", [
                            'property' => $name,
                            'missing' => $prop,
                        ]);
                }
            }
        }

        return null;
    }
}