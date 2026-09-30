<?php declare(strict_types=1);

namespace SebastianBergmann\Comparator;

use function abs;
use function assert;
use function floor;
use function sprintf;
use DateInterval;
use DateTimeInterface;
use DateTimeZone;

final class DateTimeComparator extends ObjectComparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return ($expected instanceof DateTimeInterface) &&
               ($actual instanceof DateTimeInterface);
    }

    
    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false, array &$processed = []): void
    {
        assert($expected instanceof DateTimeInterface);
        assert($actual instanceof DateTimeInterface);

        $absDelta = abs($delta);
        $delta    = new DateInterval(sprintf('PT%dS', $absDelta));
        $delta->f = $absDelta - floor($absDelta);

        $actualClone = (clone $actual)
            ->setTimezone(new DateTimeZone('UTC'));

        $expectedLower = (clone $expected)
            ->setTimezone(new DateTimeZone('UTC'))
            ->sub($delta);

        $expectedUpper = (clone $expected)
            ->setTimezone(new DateTimeZone('UTC'))
            ->add($delta);

        if ($actualClone < $expectedLower || $actualClone > $expectedUpper) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->dateTimeToString($expected),
                $this->dateTimeToString($actual),
                'Failed asserting that two DateTime objects are equal.',
            );
        }
    }

    
    private function dateTimeToString(DateTimeInterface $datetime): string
    {
        $string = $datetime->format('Y-m-d\TH:i:s.uO');

        return $string ?: 'Invalid DateTimeInterface object';
    }
}
