<?php


namespace Opis\JsonSchema;

interface Format
{
    
    public function validate($data): bool;
}