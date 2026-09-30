<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class ConstKeyword implements Keyword
{
    use ErrorTrait;

    protected $const;

    public function __construct($const)
    {
        $this->const = $const;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (Helper::equals($this->const, $context->currentData())) {
            return null;
        }

        return $this->error($schema, $context, 'const', 'The data must match the const value', [
            'const' => $this->const,
        ]);
    }
}
