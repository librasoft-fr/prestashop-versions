<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Helper;

trait ResolverTrait
{
    
    protected function resolveSubTypes(array $list): array
    {
        foreach (Helper::JSON_SUBTYPES as $sub => $super) {
            if (!isset($list[$sub]) && isset($list[$super])) {
                $list[$sub] = $list[$super];
            }
        }

        return $list;
    }
}