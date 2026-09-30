<?php


namespace Opis\JsonSchema\Pragmas;

use Opis\JsonSchema\{ValidationContext, Pragma};

class MaxErrorsPragma implements Pragma
{

    protected int $maxErrors;

    
    public function __construct(int $maxErrors)
    {
        $this->maxErrors = $maxErrors;
    }

    
    public function enter(ValidationContext $context)
    {
        $data = $context->maxErrors();
        $context->setMaxErrors($this->maxErrors);
        return $data;
    }

    
    public function leave(ValidationContext $context, $data): void
    {
        if ($data === null) {
            return;
        }
        $context->setMaxErrors($data);
    }
}