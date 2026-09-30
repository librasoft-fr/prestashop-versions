<?php


namespace Opis\JsonSchema;

interface Variables
{
    
    public function resolve($data, array $path = []);
}