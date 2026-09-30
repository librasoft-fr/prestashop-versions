<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\{ValidationContext, Schema};
use Opis\JsonSchema\Errors\{ErrorContainer, ValidationError};

trait ErrorTrait
{
    
    protected function error(
        Schema $schema,
        ValidationContext $context,
        string $keyword,
        string $message,
        array $args = [],
        $errors = null
    ): ValidationError
    {
        if ($errors) {
            if ($errors instanceof ValidationError) {
                $errors = [$errors];
            } elseif ($errors instanceof ErrorContainer) {
                $errors = $errors->all();
            }
        }

        return new ValidationError($keyword, $schema, DataInfo::fromContext($context), $message, $args,
            is_array($errors) ? $errors : []);
    }
}