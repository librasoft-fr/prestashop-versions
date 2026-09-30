<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Format,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\{ValidationError, CustomError};

class FormatKeyword implements Keyword
{
    use ErrorTrait;

    protected ?string $name;

    
    protected ?array $types;

    
    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();

        if (!isset($this->types[$type])) {
            return null;
        }

        $format = $this->types[$type];

        try {
            if ($format instanceof Format) {
                $ok = $format->validate($context->currentData());
            } else {
                $ok = $format($context->currentData());
            }
        } catch (CustomError $error) {
            return $this->error($schema, $context, 'format', $error->getMessage(), $error->getArgs() + [
                'format' => $this->name,
                'type' => $type,
            ]);
        }

        if ($ok) {
            return null;
        }

        return $this->error($schema, $context, 'format', "The data must match the '{format}' format", [
            'format' => $this->name,
            'type' => $type,
        ]);
    }
}
