<?php

declare(strict_types=1);

namespace Sentry\Context;


final class RuntimeContext
{
    
    private $name;

    
    private $version;

    
    private $sapi;

    
    public function __construct(string $name, ?string $version = null, ?string $sapi = null)
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('The $name argument cannot be an empty string.');
        }

        $this->name = $name;
        $this->version = $version;
        $this->sapi = $sapi;
    }

    
    public function getName(): string
    {
        return $this->name;
    }

    
    public function setName(string $name): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('The $name argument cannot be an empty string.');
        }

        $this->name = $name;
    }

    
    public function getVersion(): ?string
    {
        return $this->version;
    }

    
    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    
    public function getSAPI(): ?string
    {
        return $this->sapi;
    }

    
    public function setSAPI(?string $sapi): void
    {
        $this->sapi = $sapi;
    }
}
