<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Schemas;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Schema;

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
