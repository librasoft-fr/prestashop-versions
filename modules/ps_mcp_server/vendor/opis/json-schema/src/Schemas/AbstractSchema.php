<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Info\SchemaInfo;

abstract class AbstractSchema implements Schema
{

    protected SchemaInfo $info;

    
    public function __construct(SchemaInfo $info)
    {
        $this->info = $info;
    }

    
    public function info(): SchemaInfo
    {
        return $this->info;
    }
}