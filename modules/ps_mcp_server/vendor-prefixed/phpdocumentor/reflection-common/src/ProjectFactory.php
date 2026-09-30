<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection;

interface ProjectFactory
{
    public function create(string $name, array $files): Project;
}
