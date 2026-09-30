<?php


namespace Opis\JsonSchema;

interface KeywordValidator extends SchemaValidator
{
    
    public function next(): ?KeywordValidator;

    
    public function setNext(?KeywordValidator $next): KeywordValidator;
}