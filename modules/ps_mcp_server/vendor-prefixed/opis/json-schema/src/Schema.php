<?php

namespace PsMcpServerDeps\Opis\JsonSchema;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;

interface Schema extends SchemaValidator
{
    public function info(): SchemaInfo;
}
