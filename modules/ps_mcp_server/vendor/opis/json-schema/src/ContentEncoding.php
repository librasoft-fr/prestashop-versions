<?php


namespace Opis\JsonSchema;

interface ContentEncoding
{
    
    public function decode(string $value, string $type): ?string;
}