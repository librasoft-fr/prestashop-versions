<?php declare(strict_types=1);

namespace SebastianBergmann\Comparator;

use function assert;
use Exception;


final class ExceptionComparator extends ObjectComparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected instanceof Exception && $actual instanceof Exception;
    }

    protected function toArray(object $object): array
    {
        assert($object instanceof Exception);

        $array = parent::toArray($object);

        unset(
            $array['file'],
            $array['line'],
            $array['trace'],
            $array['string'],
            $array['xdebug_message'],
        );

        return $array;
    }
}
