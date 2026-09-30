<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ErrorContainer;
use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Info\DataInfo;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

trait ErrorTrait
{
    protected function error(
        Schema $schema,
        ValidationContext $context,
        string $keyword,
        string $message,
        array $args = [],
        $errors = null,
    ): ValidationError {
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
