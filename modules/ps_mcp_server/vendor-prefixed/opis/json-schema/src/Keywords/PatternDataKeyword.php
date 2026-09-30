<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\JsonPointer;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
                'pointer' => (string) $this->value,
            ]);
        }

        $this->pattern = $pattern;
        $this->regex = Helper::patternToRegex($pattern);
        $ret = parent::validate($context, $schema);
        $this->pattern = $this->regex = null;

        return $ret;
    }
}
