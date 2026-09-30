<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class AllOfKeyword implements Keyword
{
    use OfTrait;
    use ErrorTrait;

    protected array $value;

    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $object = $this->createArrayObject($context);

        foreach ($this->value as $index => $value) {
            if ($value === true) {
                continue;
            }

            if ($value === false) {
                $this->addEvaluatedFromArrayObject($object, $context);

                return $this->error($schema, $context, 'allOf', 'The data should match all schemas', [
                    'index' => $index,
                ]);
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $this->addEvaluatedFromArrayObject($object, $context);

                return $this->error($schema, $context, 'allOf', 'The data should match all schemas', [
                    'index' => $index,
                ], $error);
            }
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        return null;
    }
}
