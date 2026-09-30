<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class MultipleOfKeyword implements Keyword
{
    use ErrorTrait;

    protected float $number;

    public function __construct(float $number)
    {
        $this->number = $number;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (Helper::isMultipleOf($context->currentData(), $this->number)) {
            return null;
        }

        return $this->error($schema, $context, 'multipleOf', 'Number must be a multiple of {divisor}', [
            'divisor' => $this->number,
        ]);
    }
}
