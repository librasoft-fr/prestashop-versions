<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;


interface ConstraintInterface
{
    
    public function getErrors(): array;

    
    public function addErrors(array $errors): void;

    
    public function addError(ConstraintError $constraint, ?JsonPointer $path = null, array $more = []): void;

    
    public function isValid(): bool;

    
    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void;
}
