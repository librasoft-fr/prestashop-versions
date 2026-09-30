<?php

declare(strict_types=1);

namespace JsonMapper\Dto;


class NamedMiddleware
{
    
    private $middleware;
    
    private $name;

    public function __construct(callable $middleware, string $name)
    {
        $this->middleware = $middleware;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMiddleware(): callable
    {
        return $this->middleware;
    }
}
