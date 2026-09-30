<?php

namespace PsMcpServerDeps\Mcp\Capability\Registry\Loader;

use PsMcpServerDeps\Mcp\Capability\RegistryInterface;

interface LoaderInterface
{
    public function load(RegistryInterface $registry): void;
}
