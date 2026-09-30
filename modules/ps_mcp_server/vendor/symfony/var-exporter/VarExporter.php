<?php



namespace Symfony\Component\VarExporter;

use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\Internal\Exporter;
use Symfony\Component\VarExporter\Internal\Hydrator;
use Symfony\Component\VarExporter\Internal\Registry;
use Symfony\Component\VarExporter\Internal\Values;


final class VarExporter
{
    
    public static function export(mixed $value, ?bool &$isStaticValue = null, array &$foundClasses = []): string
    {
        $isStaticValue = true;

        if (!\is_object($value) && !(\is_array($value) && $value) && !\is_resource($value) || $value instanceof \UnitEnum) {
            return Exporter::export($value);
        }

        $objectsPool = new \SplObjectStorage();
        $refsPool = [];
        $objectsCount = 0;

        try {
            $value = Exporter::prepare([$value], $objectsPool, $refsPool, $objectsCount, $isStaticValue)[0];
        } finally {
            $references = [];
            foreach ($refsPool as $i => $v) {
                if ($v[0]->count) {
                    $references[1 + $i] = $v[2];
                }
                $v[0] = $v[1];
            }
        }

        if ($isStaticValue) {
            return Exporter::export($value);
        }

        $classes = [];
        $values = [];
        $states = [];
        foreach ($objectsPool as $i => $v) {
            [, $class, $values[], $wakeup] = $objectsPool[$v];
            $foundClasses[$class] = $classes[] = $class;

            if (0 < $wakeup) {
                $states[$wakeup] = $i;
            } elseif (0 > $wakeup) {
                $states[-$wakeup] = [$i, array_pop($values)];
                $values[] = [];
            }
        }
        ksort($states);

        $wakeups = [null];
        foreach ($states as $v) {
            if (\is_array($v)) {
                $wakeups[-$v[0]] = $v[1];
            } else {
                $wakeups[] = $v;
            }
        }

        if (null === $wakeups[0]) {
            unset($wakeups[0]);
        }

        $properties = [];
        foreach ($values as $i => $vars) {
            foreach ($vars as $class => $values) {
                foreach ($values as $name => $v) {
                    $properties[$class][$name][$i] = $v;
                }
            }
        }

        if ($classes || $references) {
            $value = new Hydrator(new Registry($classes), $references ? new Values($references) : null, $properties, $value, $wakeups);
        } else {
            $isStaticValue = true;
        }

        return Exporter::export($value);
    }
}
