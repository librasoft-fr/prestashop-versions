<?php declare(strict_types=1);

namespace SebastianBergmann\ObjectReflector;

use function count;
use function explode;

final class ObjectReflector
{
    
    public function getProperties(object $object): array
    {
        $properties = [];
        $className  = $object::class;

        foreach ((array) $object as $name => $value) {
            $name = explode("\0", (string) $name);

            if (count($name) === 1) {
                $name = $name[0];
            } elseif ($name[1] !== $className) {
                $name = $name[1] . '::' . $name[2];
            } else {
                $name = $name[2];
            }

            $properties[$name] = $value;
        }

        return $properties;
    }
}
