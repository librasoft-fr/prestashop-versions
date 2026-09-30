<?php



namespace Symfony\Component\Uid;

class NilUlid extends Ulid
{
    public function __construct()
    {
        $this->uid = parent::NIL;
    }
}
