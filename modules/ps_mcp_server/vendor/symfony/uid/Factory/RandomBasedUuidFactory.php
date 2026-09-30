<?php



namespace Symfony\Component\Uid\Factory;

use Symfony\Component\Uid\UuidV4;

class RandomBasedUuidFactory
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function create(): UuidV4
    {
        $class = $this->class;

        return new $class();
    }
}
