<?php declare(strict_types=1);

namespace SebastianBergmann\Diff;

use function gettype;
use function is_object;
use function sprintf;
use Exception;

final class ConfigurationException extends InvalidArgumentException
{
    public function __construct(
        string $option,
        string $expected,
        $value,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Option "%s" must be %s, got "%s".',
                $option,
                $expected,
                is_object($value) ? $value::class : (null === $value ? '<null>' : gettype($value) . '#' . $value),
            ),
            $code,
            $previous,
        );
    }
}
