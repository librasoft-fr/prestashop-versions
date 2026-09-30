<?php


namespace Opis\JsonSchema\KeywordValidators;

use Opis\JsonSchema\{ValidationContext, Pragma};
use Opis\JsonSchema\Errors\ValidationError;

final class PragmaKeywordValidator extends AbstractKeywordValidator
{
    
    protected array $pragmas = [];

    
    public function __construct(array $pragmas)
    {
        $this->pragmas = $pragmas;
    }

    
    public function validate(ValidationContext $context): ?ValidationError
    {
        if (!$this->next) {
            return null;
        }

        if (!$this->pragmas) {
            return $this->next->validate($context);
        }

        $data = [];

        foreach ($this->pragmas as $key => $handler) {
            $data[$key] = $handler->enter($context);
        }

        $error = $this->next->validate($context);

        foreach (array_reverse($this->pragmas, true) as $key => $handler) {
            $handler->leave($context, $data[$key] ?? null);
        }

        return $error;
    }
}