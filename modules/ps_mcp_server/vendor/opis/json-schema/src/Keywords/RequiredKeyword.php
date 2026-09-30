<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class RequiredKeyword implements Keyword
{
    use ErrorTrait;

    
    protected ?array $required;

    
    public function __construct(array $required)
    {
        $this->required = $required;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $max = $context->maxErrors();
        $list = [];

        foreach ($this->required as $name) {
            if (!property_exists($data, $name)) {
                $list[] = $name;
                if (--$max <= 0) {
                    break;
                }
            }
        }

        if (!$list) {
            return null;
        }

        return $this->error($schema, $context, 'required', 'The required properties ({missing}) are missing', [
            'missing' => $list,
        ]);
    }
}