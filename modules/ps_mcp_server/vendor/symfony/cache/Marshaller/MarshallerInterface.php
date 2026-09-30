<?php



namespace Symfony\Component\Cache\Marshaller;


interface MarshallerInterface
{
    
    public function marshall(array $values, ?array &$failed): array;

    
    public function unmarshall(string $value): mixed;
}
