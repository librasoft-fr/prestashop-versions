<?php



namespace Symfony\Component\Uid;


class NilUuid extends Uuid
{
    protected const TYPE = -1;

    public function __construct()
    {
        $this->uid = parent::NIL;
    }
}
