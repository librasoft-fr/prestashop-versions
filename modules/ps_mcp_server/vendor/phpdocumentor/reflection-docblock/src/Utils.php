<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\Exception\PcreException;
use Webmozart\Assert\Assert;

use function preg_last_error;
use function preg_split as php_preg_split;

abstract class Utils
{
    
    public static function pregSplit(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        $parts = php_preg_split($pattern, $subject, $limit, $flags);
        if ($parts === false) {
            throw PcreException::createFromPhpError(preg_last_error());
        }

        Assert::allString($parts);

        return $parts;
    }
}
