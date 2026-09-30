<?php



namespace Symfony\Component\Uid;


interface TimeBasedUidInterface
{
    public function getDateTime(): \DateTimeImmutable;
}
