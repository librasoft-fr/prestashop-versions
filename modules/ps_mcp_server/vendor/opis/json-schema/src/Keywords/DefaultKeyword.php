<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class DefaultKeyword implements Keyword
{

    protected array $defaults;

    
    public function __construct(array $defaults)
    {
        $this->defaults = $defaults;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();

        if (is_object($data)) {
            foreach ($this->defaults as $name => $value) {
                if (!property_exists($data, $name)) {
                    $data->{$name} = Helper::cloneValue($value);
                }
            }
        }

        return null;
    }
}