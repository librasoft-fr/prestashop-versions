<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class PropertiesKeyword implements Keyword
{
    use IterableDataValidationTrait;

    protected array $properties;
    protected array $propertyKeys;

    public function __construct(array $properties)
    {
        $this->properties = $properties;
        $this->propertyKeys = array_keys($properties);
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!$this->properties) {
            return null;
        }

        $checked = [];
        $evaluated = [];

        $data = $context->currentData();

        $errors = $this->errorContainer($context->maxErrors());

        foreach ($this->properties as $name => $prop) {
            if (!property_exists($data, $name)) {
                continue;
            }

            $checked[] = $name;

            if ($prop === true) {
                $evaluated[] = $name;
                continue;
            }

            if ($prop === false) {
                $context->addEvaluatedProperties($evaluated);

                return $this->error($schema, $context, 'properties', "Property '{property}' is not allowed", [
                    'property' => $name,
                ]);
            }

            if (is_object($prop) && !($prop instanceof Schema)) {
                $prop = $this->properties[$name] = $context->loader()->loadObjectSchema($prop);
            }

            $context->pushDataPath($name);
            $error = $prop->validate($context);
            $context->popDataPath();

            if ($error) {
                $errors->add($error);
                if ($errors->isFull()) {
                    break;
                }
            } else {
                $evaluated[] = $name;
            }
        }

        $context->addEvaluatedProperties($evaluated);

        if (!$errors->isEmpty()) {
            return $this->error($schema, $context, 'properties', 'The properties must match schema: {properties}', [
                'properties' => array_values(array_diff($checked, $evaluated)),
            ], $errors);
        }
        unset($errors);

        $context->addCheckedProperties($checked);

        return null;
    }
}
