<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class OneOfKeyword implements Keyword
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
        $count = 0;
        $matchedIndex = -1;
        $object = $this->createArrayObject($context);
        $errors = [];

        foreach ($this->value as $index => $value) {
            if ($value === false) {
                continue;
            }

            if ($value === true) {
                if (++$count > 1) {
                    $this->addEvaluatedFromArrayObject($object, $context);

                    return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
                        'matched' => [$matchedIndex, $index],
                    ]);
                }

                $matchedIndex = $index;
                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            $error = $context->validateSchemaWithoutEvaluated($value, null, false, $object);
            if ($error) {
                $errors[] = $error;
            } else {
                if (++$count > 1) {
                    $this->addEvaluatedFromArrayObject($object, $context);

                    return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
                        'matched' => [$matchedIndex, $index],
                    ]);
                }
                $matchedIndex = $index;
            }
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        if ($count === 1) {
            return null;
        }

        return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
            'matched' => [],
        ], $errors);
    }
}
