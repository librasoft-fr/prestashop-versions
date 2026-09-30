<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class AnyOfKeyword implements Keyword
{
    use OfTrait;
    use ErrorTrait;

    
    protected array $value;
    protected bool $alwaysValid;

    
    public function __construct(array $value, bool $alwaysValid = false)
    {
        $this->value = $value;
        $this->alwaysValid = $alwaysValid;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $object = $this->createArrayObject($context);
        if ($this->alwaysValid && !$object) {
            return null;
        }

        $errors = [];
        $ok = false;

        foreach ($this->value as $index => $value) {
            if ($value === true) {
                $ok = true;
                if ($object) {
                    continue;
                }
                return null;
            }

            if ($value === false) {
                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $errors[] = $error;
                continue;
            }

            if (!$object) {
                return null;
            }
            $ok = true;
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        if ($ok) {
            return null;
        }

        return $this->error($schema, $context, 'anyOf', 'The data should match at least one schema', [], $errors);
    }
}