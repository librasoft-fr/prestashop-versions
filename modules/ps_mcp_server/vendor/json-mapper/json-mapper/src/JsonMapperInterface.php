<?php

declare(strict_types=1);

namespace JsonMapper;

interface JsonMapperInterface
{
    public function setPropertyMapper(callable $propertyMapper): JsonMapperInterface;

    public function push(callable $middleware, string $name = ''): self;

    public function pop(): self;

    public function unshift(callable $middleware, string $name = ''): self;

    public function shift(): self;

    public function remove(callable $remove): self;

    public function removeByName(string $remove): self;

    
    public function mapObject(\stdClass $json, $object);

    
    public function mapToClass(\stdClass $json, string $class);

    
    public function mapArray(array $json, $object): array;

    
    public function mapToClassArray(array $json, string $class): array;

    
    public function mapObjectFromString(string $json, $object);

    
    public function mapToClassFromString(string $json, string $class);

    
    public function mapArrayFromString(string $json, $object): array;

    
    public function mapToClassArrayFromString(string $json, string $class): array;
}
