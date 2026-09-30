<?php


namespace Opis\JsonSchema;

interface ContentMediaType
{
    
    public function validate(string $content, string $media_type): bool;
}