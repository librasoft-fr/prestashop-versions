<?php

declare(strict_types=1);

namespace JsonSchema\Tool\Validator;

class RelativeReferenceValidator
{
    public static function isValid(string $ref): bool
    {
        
        $pattern = '/^(([^\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/';

        if (preg_match($pattern, $ref) !== 1) {
            return false;
        }

        
        if (strpos($ref, '\\') !== false) {
            return false; 
        }

        if (preg_match('/^(http|https):\/\//', $ref)) {
            return false; 
        }

        if (preg_match('/^:\/\//', $ref)) {
            return false; 
        }

        if (preg_match('/^:\//', $ref)) {
            return false; 
        }

        if (preg_match('/^\/\/$/', $ref)) {
            return false; 
        }

        if (preg_match('/^\/\/\/[^\/]/', $ref)) {
            return false; 
        }

        if (preg_match('/\s/', $ref)) {
            return false; 
        }

        return true;
    }
}
