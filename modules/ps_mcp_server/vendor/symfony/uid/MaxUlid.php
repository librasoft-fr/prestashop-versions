<?php



namespace Symfony\Component\Uid;

class MaxUlid extends Ulid
{
    public function __construct()
    {
        $this->uid = parent::MAX;
    }
}
