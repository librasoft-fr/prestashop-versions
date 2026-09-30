<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class ConstDataKeyword extends ConstKeyword
{

    protected JsonPointer $value;

    
    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct(null);
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($value === $this) {
            return $this->error($schema, $context, 'const', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->const = $value;
        $ret = parent::validate($context, $schema);
        $this->const = null;

        return $ret;
    }
}