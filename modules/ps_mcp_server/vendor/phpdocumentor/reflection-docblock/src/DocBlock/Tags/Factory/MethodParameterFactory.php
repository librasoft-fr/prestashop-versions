<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use function array_key_last;
use function get_class;
use function gettype;
use function method_exists;
use function ucfirst;
use function var_export;


final class MethodParameterFactory
{
    
    public function format($defaultValue): string
    {
        $method = 'format' . ucfirst(gettype($defaultValue));
        if (method_exists($this, $method)) {
            return ' = ' . $this->{$method}($defaultValue);
        }

        return '';
    }

    private function formatDouble(float $defaultValue): string
    {
        return var_export($defaultValue, true);
    }

    
    private function formatNull($defaultValue): string
    {
        return 'null';
    }

    private function formatInteger(int $defaultValue): string
    {
        return var_export($defaultValue, true);
    }

    private function formatString(string $defaultValue): string
    {
        return var_export($defaultValue, true);
    }

    private function formatBoolean(bool $defaultValue): string
    {
        return var_export($defaultValue, true);
    }

    
    private function formatArray(array $defaultValue): string
    {
        $formatedValue = '[';

        foreach ($defaultValue as $key => $value) {
            $method = 'format' . ucfirst(gettype($value));
            if (!method_exists($this, $method)) {
                continue;
            }

            $formatedValue .= $this->{$method}($value);

            if ($key === array_key_last($defaultValue)) {
                continue;
            }

            $formatedValue .= ',';
        }

        return $formatedValue . ']';
    }

    private function formatObject(object $defaultValue): string
    {
        return 'new ' . get_class($defaultValue) . '()';
    }
}
