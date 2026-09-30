<?php



namespace Mcp\Capability\Registry\Loader;

use Mcp\Capability\RegistryInterface;


interface LoaderInterface
{
    public function load(RegistryInterface $registry): void;
}
