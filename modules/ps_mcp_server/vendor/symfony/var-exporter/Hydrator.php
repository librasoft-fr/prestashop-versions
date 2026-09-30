<?php



namespace Symfony\Component\VarExporter;

use Symfony\Component\VarExporter\Internal\Hydrator as InternalHydrator;


final class Hydrator
{
    
    public static function hydrate(object $instance, array $properties = [], array $scopedProperties = []): object
    {
        if ($properties) {
            $class = $instance::class;
            $propertyScopes = InternalHydrator::$propertyScopes[$class] ??= InternalHydrator::getPropertyScopes($class);

            foreach ($properties as $name => &$value) {
                [$scope, $name, $writeScope] = $propertyScopes[$name] ?? [$class, $name, $class];
                $scopedProperties[$writeScope ?? $scope][$name] = &$value;
            }
            unset($value);
        }

        foreach ($scopedProperties as $scope => $properties) {
            if ($properties) {
                (InternalHydrator::$simpleHydrators[$scope] ??= InternalHydrator::getSimpleHydrator($scope))($properties, $instance);
            }
        }

        return $instance;
    }
}
