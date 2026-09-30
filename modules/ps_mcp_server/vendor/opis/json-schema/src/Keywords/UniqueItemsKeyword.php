<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    Helper,
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

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

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
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