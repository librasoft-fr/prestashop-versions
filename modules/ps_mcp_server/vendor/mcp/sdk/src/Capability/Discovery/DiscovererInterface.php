<?php



namespace Mcp\Capability\Discovery;


interface DiscovererInterface
{
    
    public function discover(string $basePath, array $directories, array $excludeDirs = []): DiscoveryState;
}
