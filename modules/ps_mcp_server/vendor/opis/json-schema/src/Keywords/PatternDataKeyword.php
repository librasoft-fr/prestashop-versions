<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{Helper, ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class PatternDataKeyword extends PatternKeyword
{

    protected JsonPointer $value;

    
    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct('');
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $pattern = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($pattern === $this || !is_string($pattern) || !Helper::isValidPattern($pattern)) {
            return $this->error($schema, $context, 'pattern', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->pattern = $pattern;
        $this->regex = Helper::patternToRegex($pattern);
        $ret = parent::validate($context, $schema);
        $this->pattern = $this->regex = null;

        return $ret;
    }
}