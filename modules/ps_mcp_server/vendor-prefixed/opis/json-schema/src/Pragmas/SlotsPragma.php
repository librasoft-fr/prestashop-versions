<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Pragmas;

use PsMcpServerDeps\Opis\JsonSchema\Pragma;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class SlotsPragma implements Pragma
{
    protected array $slots;

    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    public function enter(ValidationContext $context)
    {
        $data = $context->slots();
        $context->setSlots($data ? $this->slots + $data : $this->slots);

        return $data;
    }

    public function leave(ValidationContext $context, $data): void
    {
        $context->setSlots($data);
    }
}
