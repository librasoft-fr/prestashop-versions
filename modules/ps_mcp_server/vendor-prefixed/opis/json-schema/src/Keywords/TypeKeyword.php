<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class TypeKeyword implements Keyword
{
    use ErrorTrait;

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();
        if ($type && Helper::jsonTypeMatches($type, $this->type)) {
            return null;
        }

        return $this->error($schema, $context, 'type', 'The data ({type}) must match the type: {expected}', [
            'expected' => $this->type,
            'type' => $type,
        ]);
    }
}
