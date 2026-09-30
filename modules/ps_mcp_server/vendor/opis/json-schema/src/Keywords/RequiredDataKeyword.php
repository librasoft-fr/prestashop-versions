<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class RequiredDataKeyword extends RequiredKeyword
{

    protected JsonPointer $value;

    
    protected $filter;

    
    public function __construct(JsonPointer $value, ?callable $filter = null)
    {
        $this->value = $value;
        $this->filter = $filter;
        parent::__construct([]);
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $required = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($required === $this || !is_array($required) || !$this->requiredPropsAreValid($required)) {
            return $this->error($schema, $context, 'required', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $required = array_unique($required);

        if ($this->filter) {
            $required = array_filter($required, $this->filter);
        }

        if (!$required) {
            return null;
        }

        $this->required = $required;
        $ret = parent::validate($context, $schema);
        $this->required = null;

        return $ret;
    }

    
    protected function requiredPropsAreValid(array $props): bool
    {
        foreach ($props as $prop) {
            if (!is_string($prop)) {
                return false;
            }
        }

        return true;
    }
}