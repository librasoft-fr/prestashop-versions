<?php

namespace PsMcpServerDeps\Opis\JsonSchema;

interface Format
{
    public function validate($data): bool;
}
