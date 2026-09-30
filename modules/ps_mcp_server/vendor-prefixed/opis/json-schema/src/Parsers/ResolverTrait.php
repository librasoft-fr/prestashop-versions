<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Helper;

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
