<?php

declare(strict_types=1);

namespace Sentry\State;

use Sentry\ClientInterface;


final class Layer
{
    
    private $client;

    
    private $scope;

    
    public function __construct(?ClientInterface $client, Scope $scope)
    {
        $this->client = $client;
        $this->scope = $scope;
    }

    
    public function getClient(): ?ClientInterface
    {
        return $this->client;
    }

    
    public function setClient(?ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    
    public function getScope(): Scope
    {
        return $this->scope;
    }

    
    public function setScope(Scope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }
}
