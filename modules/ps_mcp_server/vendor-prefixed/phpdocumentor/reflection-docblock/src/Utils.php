<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection;

use PsMcpServerDeps\phpDocumentor\Reflection\Exception\PcreException;
use PsMcpServerDeps\Webmozart\Assert\Assert;

use function preg_split as php_preg_split;

abstract class Utils
{
    public static function pregSplit(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        $parts = php_preg_split($pattern, $subject, $limit, $flags);
        if ($parts === false) {
            throw PcreException::createFromPhpError(\preg_last_error());
        }

        Assert::allString($parts);

        return $parts;
    }
}
