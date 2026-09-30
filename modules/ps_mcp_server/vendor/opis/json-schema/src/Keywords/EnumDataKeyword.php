<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class EnumDataKeyword extends EnumKeyword
{

    protected JsonPointer $value;

    
    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct([]);
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($value === $this || !is_array($value) || empty($value)) {
            return $this->error($schema, $context, 'enum', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->enum = $this->listByType($value);
        $ret = parent::validate($context, $schema);
        $this->enum = null;

        return $ret;
    }
}