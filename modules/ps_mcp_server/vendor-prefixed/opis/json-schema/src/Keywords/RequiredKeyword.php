<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class RequiredKeyword implements Keyword
{
    use ErrorTrait;

    protected ?array $required;

    public function __construct(array $required)
    {
        $this->required = $required;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $max = $context->maxErrors();
        $list = [];

        foreach ($this->required as $name) {
            if (!property_exists($data, $name)) {
                $list[] = $name;
                if (--$max <= 0) {
                    break;
                }
            }
        }

        if (!$list) {
            return null;
        }

        return $this->error($schema, $context, 'required', 'The required properties ({missing}) are missing', [
            'missing' => $list,
        ]);
    }
}
