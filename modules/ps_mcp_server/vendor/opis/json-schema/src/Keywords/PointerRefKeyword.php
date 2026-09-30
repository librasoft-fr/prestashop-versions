<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{JsonPointer, Schema, ValidationContext, Variables};

class PointerRefKeyword extends AbstractRefKeyword
{
    protected JsonPointer $pointer;
    
    protected $resolved = false;

    public function __construct(
        JsonPointer $pointer,
        ?Variables $mapper,
        ?Variables $globals,
        ?array $slots = null,
        string $keyword = '$ref'
    ) {
        parent::__construct($mapper, $globals, $slots, $keyword);
        $this->pointer = $pointer;
    }

    protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->resolved === false) {
            $info = $schema->info();
            $this->resolved = $this->resolvePointer($context->loader(), $this->pointer, $info->idBaseRoot(), $info->path());
        }

        if ($this->resolved === null) {
            throw new UnresolvedReferenceException((string)$this->pointer, $schema, $context);
        }

        return $this->resolved->validate($this->createContext($context, $schema));
    }
}