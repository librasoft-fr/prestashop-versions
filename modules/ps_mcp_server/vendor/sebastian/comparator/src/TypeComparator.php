<?php declare(strict_types=1);

namespace SebastianBergmann\Comparator;

use function gettype;
use function sprintf;
use SebastianBergmann\Exporter\Exporter;

final class TypeComparator extends Comparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return true;
    }

    
    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        if (gettype($expected) != gettype($actual)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                
                '',
                '',
                sprintf(
                    '%s does not match expected type "%s".',
                    (new Exporter)->shortenedExport($actual),
                    gettype($expected),
                ),
            );
        }
    }
}
