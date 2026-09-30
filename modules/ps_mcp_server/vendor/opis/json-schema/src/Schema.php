<?php


namespace Opis\JsonSchema;

use Opis\JsonSchema\Info\SchemaInfo;

interface Schema extends SchemaValidator
{
    
    public function info(): SchemaInfo;
}