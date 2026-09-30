<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class IfThenElseKeyword implements Keyword
{
    use ErrorTrait;

    
    protected $if;

    
    protected $then;

    
    protected $else;

    
    public function __construct($if, $then, $else)
    {
        $this->if = $if;
        $this->then = $then;
        $this->else = $else;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->if === true) {
            return $this->validateBranch('then', $context, $schema);
        } elseif ($this->if === false) {
            return $this->validateBranch('else', $context, $schema);
        }

        if (is_object($this->if) && !($this->if instanceof Schema)) {
            $this->if = $context->loader()->loadObjectSchema($this->if);
        }

        if ($context->validateSchemaWithoutEvaluated($this->if, null, true)) {
            return $this->validateBranch('else', $context, $schema);
        }

        return $this->validateBranch('then', $context, $schema);
    }

    
    protected function validateBranch(string $branch, ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->{$branch};

        if ($value === true) {
            return null;
        } elseif ($value === false) {
            return $this->error($schema, $context, $branch, "The data is never valid on '{branch}' branch", [
                'branch' => $branch,
            ]);
        }

        if (is_object($value) && !($value instanceof Schema)) {
            $value = $this->{$branch} = $context->loader()->loadObjectSchema($value);
        }

        if ($error = $value->validate($context)) {
            return $this->error($schema, $context, $branch, "The data is not valid on '{branch}' branch", [
                'branch' => $branch,
            ], $error);
        }

        return null;
    }
}