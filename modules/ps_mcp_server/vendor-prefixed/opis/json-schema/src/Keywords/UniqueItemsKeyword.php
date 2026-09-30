<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class UniqueItemsKeyword implements Keyword
{
    use ErrorTrait;

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        if (!$data) {
            return null;
        }

        $count = count($data);

        for ($i = 0; $i < $count - 1; ++$i) {
            for ($j = $i + 1; $j < $count; ++$j) {
                if (Helper::equals($data[$i], $data[$j])) {
                    return $this->error($schema, $context, 'uniqueItems', 'Array must have unique items', [
                        'duplicate' => $data[$i],
                        'indexes' => [$i, $j],
                    ]);
                }
            }
        }

        return null;
    }
}
