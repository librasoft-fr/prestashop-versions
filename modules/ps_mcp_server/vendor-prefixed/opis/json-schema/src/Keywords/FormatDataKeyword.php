<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\JsonPointer;
use PsMcpServerDeps\Opis\JsonSchema\Resolvers\FormatResolver;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class FormatDataKeyword extends FormatKeyword
{
    protected JsonPointer $value;

    protected FormatResolver $resolver;

    public function __construct(JsonPointer $value, FormatResolver $resolver)
    {
        $this->value = $value;
        $this->resolver = $resolver;
        parent::__construct('', []);
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($value === $this || !is_string($value)) {
            return $this->error($schema, $context, 'format', 'Invalid $data', [
                'pointer' => (string) $this->value,
            ]);
        }

        $type = $context->currentDataType();

        $types = [
            $type => $this->resolver->resolve($value, $type),
        ];

        if (!$types[$type] && ($super = Helper::getJsonSuperType($type))) {
            $types[$super] = $this->resolver->resolve($value, $super);
            unset($super);
        }

        unset($type);

        $this->name = $value;
        $this->types = $types;
        $ret = parent::validate($context, $schema);
        $this->name = $this->types = null;

        return $ret;
    }
}
