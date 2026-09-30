<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class DependenciesKeyword implements Keyword
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
        $data = $context->currentData();
        $object = $this->createArrayObject($context);

        foreach ($this->value as $name => $value) {
            if ($value === true || !property_exists($data, $name)) {
                continue;
            }

            if ($value === false) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependencies', "Property '{property}' is not allowed", [
                    'property' => $name,
                ]);
            }

            if (is_array($value)) {
                foreach ($value as $prop) {
                    if (!property_exists($data, $prop)) {
                        $this->addEvaluatedFromArrayObject($object, $context);
                        return $this->error($schema, $context, 'dependencies',
                            "Property '{missing}' property is required by property '{property}'", [
                                'property' => $name,
                                'missing' => $prop,
                            ]);
                    }
                }

                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$name] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependencies',
                    "The object must match dependency schema defined on property '{property}'", [
                        'property' => $name,
                    ], $error);
            }
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        return null;
    }
}