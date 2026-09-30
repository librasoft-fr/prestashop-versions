<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class ExclusiveMaximumKeyword implements Keyword
{
    use ErrorTrait;

    protected float $number;

    public function __construct(float $number)
    {
        $this->number = $number;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($context->currentData() < $this->number) {
            return null;
        }

        return $this->error($schema, $context, 'exclusiveMaximum', 'Number must be lower than {max}', [
            'max' => $this->number,
        ]);
    }
}
