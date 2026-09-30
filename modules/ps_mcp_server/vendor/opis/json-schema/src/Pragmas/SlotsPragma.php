<?php


namespace Opis\JsonSchema\Pragmas;

use Opis\JsonSchema\{ValidationContext, Pragma};

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