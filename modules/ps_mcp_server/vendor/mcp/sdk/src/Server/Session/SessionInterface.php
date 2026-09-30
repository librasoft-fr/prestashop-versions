<?php



namespace Mcp\Server\Session;

use Symfony\Component\Uid\Uuid;


interface SessionInterface extends \JsonSerializable
{
    
    public function getId(): Uuid;

    
    public function save(): bool;

    
    public function get(string $key, mixed $default = null): mixed;

    
    public function set(string $key, mixed $value, bool $overwrite = true): void;

    
    public function has(string $key): bool;

    
    public function forget(string $key): void;

    
    public function clear(): void;

    
    public function pull(string $key, mixed $default = null): mixed;

    
    public function all(): array;

    
    public function hydrate(array $attributes): void;
}
