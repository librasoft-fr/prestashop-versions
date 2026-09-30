<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\ConstraintError;
use JsonSchema\Entity\JsonPointer;


class StringConstraint extends Constraint
{
    
    public function check(&$element, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        
        if (isset($schema->maxLength) && $this->strlen($element) > $schema->maxLength) {
            $this->addError(ConstraintError::LENGTH_MAX(), $path, [
                'maxLength' => $schema->maxLength,
            ]);
        }

        
        if (isset($schema->minLength) && $this->strlen($element) < $schema->minLength) {
            $this->addError(ConstraintError::LENGTH_MIN(), $path, [
                'minLength' => $schema->minLength,
            ]);
        }

        
        if (isset($schema->pattern) && !preg_match(self::jsonPatternToPhpRegex($schema->pattern), $element)) {
            $this->addError(ConstraintError::PATTERN(), $path, [
                'pattern' => $schema->pattern,
            ]);
        }

        $this->checkFormat($element, $schema, $path, $i);
    }

    private function strlen($string)
    {
        if (extension_loaded('mbstring')) {
            return mb_strlen($string, mb_detect_encoding($string));
        }

        
        return strlen($string); 
    }
}
