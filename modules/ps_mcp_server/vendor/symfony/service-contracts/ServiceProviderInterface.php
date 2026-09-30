<?php



namespace Symfony\Contracts\Service;

use Psr\Container\ContainerInterface;


interface ServiceProviderInterface extends ContainerInterface
{
    
    public function get(string $id): mixed;

    public function has(string $id): bool;

    
    public function getProvidedServices(): array;
}
