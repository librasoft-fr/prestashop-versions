<?php declare(strict_types=1);

namespace SebastianBergmann\CliParser;

use function sprintf;
use RuntimeException;

final class UnknownOptionException extends RuntimeException implements Exception
{
    public function __construct(string $option)
    {
        parent::__construct(
            sprintf(
                'Unknown option "%s"',
                $option,
            ),
        );
    }
}
