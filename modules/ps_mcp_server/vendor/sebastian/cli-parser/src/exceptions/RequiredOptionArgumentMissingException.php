<?php declare(strict_types=1);

namespace SebastianBergmann\CliParser;

use function sprintf;
use RuntimeException;

final class RequiredOptionArgumentMissingException extends RuntimeException implements Exception
{
    public function __construct(string $option)
    {
        parent::__construct(
            sprintf(
                'Required argument for option "%s" is missing',
                $option,
            ),
        );
    }
}
