<?php declare(strict_types=1);



namespace Composer\Config;


interface ConfigSourceInterface
{
    
    public function addRepository(string $name, $config, bool $append = true): void;

    
    public function insertRepository(string $name, $config, string $referenceName, int $offset = 0): void;

    
    public function setRepositoryUrl(string $name, string $url): void;

    
    public function removeRepository(string $name): void;

    
    public function addConfigSetting(string $name, $value): void;

    
    public function removeConfigSetting(string $name): void;

    
    public function addProperty(string $name, $value): void;

    
    public function removeProperty(string $name): void;

    
    public function addLink(string $type, string $name, string $value): void;

    
    public function removeLink(string $type, string $name): void;

    
    public function getName(): string;
}
