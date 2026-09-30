<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class DependentSchemasKeyword implements Keyword
{
    use OfTrait;
    use ErrorTrait;

    protected array $value;

    public function __construct(object $value)
    {
        $this->value = (array)$value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $object = $this->createArrayObject($context);

        foreach ($this->value as $name => $value) {
            if ($value === true || !property_exists($data, $name)) {
                continue;
            }

            if ($value === false) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependentSchemas', "'{$name}' property is not allowed", [
                    'property' => $name,
                ]);
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$name] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependentSchemas',
                    "The object must match dependency schema defined on property '{$name}'", [
                        'property' => $name,
                    ], $error);
            }
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        return null;
    }
}