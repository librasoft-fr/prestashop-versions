<?php declare(strict_types=1);

namespace SebastianBergmann\CliParser;

use function sprintf;
use RuntimeException;

final class OptionDoesNotAllowArgumentException extends RuntimeException implements Exception
{
    public function __construct(string $option)
    {
        parent::__construct(
            sprintf(
                'Option "%s" does not allow an argument',
                $option,
            ),
        );
    }
}
