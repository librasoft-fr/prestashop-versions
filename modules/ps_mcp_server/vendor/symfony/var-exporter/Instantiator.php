<?php



namespace Symfony\Component\VarExporter;

use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\Exception\NotInstantiableTypeException;
use Symfony\Component\VarExporter\Internal\Registry;


final class Instantiator
{
    
    public static function instantiate(string $class, array $properties = [], array $scopedProperties = []): object
    {
        $reflector = Registry::$reflectors[$class] ??= Registry::getClassReflector($class);

        if (Registry::$cloneable[$class]) {
            $instance = clone Registry::$prototypes[$class];
        } elseif (Registry::$instantiableWithoutConstructor[$class]) {
            $instance = $reflector->newInstanceWithoutConstructor();
        } elseif (null === Registry::$prototypes[$class]) {
            throw new NotInstantiableTypeException($class);
        } elseif ($reflector->implementsInterface('Serializable') && !method_exists($class, '__unserialize')) {
            $instance = unserialize('C:'.\strlen($class).':"'.$class.'":0:{}');
        } else {
            $instance = unserialize('O:'.\strlen($class).':"'.$class.'":0:{}');
        }

        return $properties || $scopedProperties ? Hydrator::hydrate($instance, $properties, $scopedProperties) : $instance;
    }
}
