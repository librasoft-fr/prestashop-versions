<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class EnumKeyword implements Keyword
{
    use ErrorTrait;

    protected ?array $enum;

    public function __construct(array $enum)
    {
        $this->enum = $this->listByType($enum);
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();
        $data = $context->currentData();

        if (isset($this->enum[$type])) {
            foreach ($this->enum[$type] as $value) {
                if (Helper::equals($value, $data)) {
                    return null;
                }
            }
        }

        return $this->error($schema, $context, 'enum', 'The data should match one item from enum');
    }

    protected function listByType(array $values): array
    {
        $list = [];

        foreach ($values as $value) {
            $type = Helper::getJsonType($value);
            if (!isset($list[$type])) {
                $list[$type] = [];
            }
            $list[$type][] = $value;
        }

        return $list;
    }
}
