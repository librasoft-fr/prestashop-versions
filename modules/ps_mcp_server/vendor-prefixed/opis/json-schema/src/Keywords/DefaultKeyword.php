<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class DefaultKeyword implements Keyword
{
    protected array $defaults;

    public function __construct(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();

        if (is_object($data)) {
            foreach ($this->defaults as $name => $value) {
                if (!property_exists($data, $name)) {
                    $data->{$name} = Helper::cloneValue($value);
                }
            }
        }

        return null;
    }
}
