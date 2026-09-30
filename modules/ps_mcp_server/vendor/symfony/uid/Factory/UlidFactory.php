<?php



namespace Symfony\Component\Uid\Factory;

use Symfony\Component\Uid\Ulid;

class UlidFactory
{
    public function create(?\DateTimeInterface $time = null): Ulid
    {
        return new Ulid(null === $time ? null : Ulid::generate($time));
    }
}
