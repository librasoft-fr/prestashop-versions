<?php



namespace Mcp\Capability\Registry\Loader;

use Mcp\Capability\Discovery\DiscovererInterface;
use Mcp\Capability\RegistryInterface;


final class DiscoveryLoader implements LoaderInterface
{
    
    public function __construct(
        private string $basePath,
        private array $scanDirs,
        private array $excludeDirs,
        private DiscovererInterface $discoverer,
    ) {
    }

    public function load(RegistryInterface $registry): void
    {
        $discoveryState = $this->discoverer->discover($this->basePath, $this->scanDirs, $this->excludeDirs);

        $registry->setDiscoveryState($discoveryState);
    }
}
