<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Pragmas;

use PsMcpServerDeps\Opis\JsonSchema\Pragma;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
