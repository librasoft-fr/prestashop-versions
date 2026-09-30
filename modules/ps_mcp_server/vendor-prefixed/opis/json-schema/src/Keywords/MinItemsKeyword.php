<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class MinItemsKeyword implements Keyword
{
    use ErrorTrait;

    protected int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = count($context->currentData());

        if ($count >= $this->count) {
            return null;
        }

        return $this->error($schema, $context, 'minItems',
            'Array should have at least {min} items, {count} found', [
                'min' => $this->count,
                'count' => $count,
            ]);
    }
}
