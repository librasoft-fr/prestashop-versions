<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class MaxPropertiesKeywords implements Keyword
{
    use ErrorTrait;

    protected int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = count($context->getObjectProperties());

        if ($count <= $this->count) {
            return null;
        }

        return $this->error($schema, $context, 'maxProperties',
            'Object must have at most {max} properties, {count} found', [
                'max' => $this->count,
                'count' => $count,
            ]);
    }
}
