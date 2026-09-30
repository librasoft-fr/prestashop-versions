<?php



namespace Symfony\Component\Cache\Adapter;


final class ParameterNormalizer
{
    public static function normalizeDuration(string $duration): int
    {
        if (is_numeric($duration)) {
            return $duration;
        }

        if (false !== $time = strtotime($duration, 0)) {
            return $time;
        }

        try {
            return \DateTimeImmutable::createFromFormat('U', 0)->add(new \DateInterval($duration))->getTimestamp();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf('Cannot parse date interval "%s".', $duration), 0, $e);
        }
    }
}
