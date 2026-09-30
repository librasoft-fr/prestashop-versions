<?php


namespace Opis\JsonSchema;

interface Pragma
{
    
    public function enter(ValidationContext $context);

    
    public function leave(ValidationContext $context, $data): void;
}