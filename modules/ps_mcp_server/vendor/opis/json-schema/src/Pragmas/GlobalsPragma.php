<?php


namespace Opis\JsonSchema\Pragmas;

use Opis\JsonSchema\{ValidationContext, Pragma};
use Opis\JsonSchema\Variables;

class GlobalsPragma implements Pragma
{

    protected Variables $globals;

    
    public function __construct(Variables $globals)
    {
        $this->globals = $globals;
    }

    
    public function enter(ValidationContext $context)
    {
        $resolved = (array) $this->globals->resolve($context->rootData(), $context->currentDataPath());
        if (!$resolved) {
            return null;
        }

        $data = $context->globals();
        $context->setGlobals($resolved, false);
        return $data;
    }

    
    public function leave(ValidationContext $context, $data): void
    {
        if ($data === null) {
            return;
        }
        $context->setGlobals($data, true);
    }
}