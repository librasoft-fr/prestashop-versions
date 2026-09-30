<?php



namespace Symfony\Component\VarExporter\Internal;


class Reference
{
    public int $count = 0;

    public function __construct(
        public readonly int $id,
        public readonly mixed $value = null,
    ) {
    }
}
