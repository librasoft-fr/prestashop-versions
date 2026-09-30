<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Exception;

final class PcreException extends \InvalidArgumentException
{
    public static function createFromPhpError(int $errorCode): self
    {
        switch ($errorCode) {
            case \PREG_BACKTRACK_LIMIT_ERROR:
                return new self('Backtrack limit error');

            case \PREG_RECURSION_LIMIT_ERROR:
                return new self('Recursion limit error');

            case \PREG_BAD_UTF8_ERROR:
                return new self('Bad UTF8 error');

            case \PREG_BAD_UTF8_OFFSET_ERROR:
                return new self('Bad UTF8 offset error');

            case \PREG_JIT_STACKLIMIT_ERROR:
                return new self('Jit stacklimit error');

            case \PREG_NO_ERROR:
            case \PREG_INTERNAL_ERROR:
            default:
        }

        return new self('Unknown Pcre error');
    }
}
