<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class ContentSchemaKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $value;

    
    public function __construct(object $value)
    {
        $this->value = $value;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = json_decode($context->getDecodedContent(), false);

        if ($error = json_last_error() !== JSON_ERROR_NONE) {
            $message = json_last_error_msg();

            return $this->error($schema, $context, 'contentSchema', "Invalid JSON content: {message}", [
                'error' => $error,
                'message' => $message,
            ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        if ($error = $this->value->validate($context->newInstance($data, $schema))) {
            return $this->error($schema, $context, 'contentSchema', "The JSON content must match schema", [], $error);
        }

        return null;
    }
}