<?php

namespace Http\Discovery\Strategy;

use Http\Discovery\ClassDiscovery;
use Http\Discovery\Exception\PuliUnavailableException;
use Puli\Discovery\Api\Discovery;
use Puli\GeneratedPuliFactory;


class PuliBetaStrategy implements DiscoveryStrategy
{
    
    protected static $puliFactory;

    
    protected static $puliDiscovery;

    
    private static function getPuliFactory()
    {
        if (null === self::$puliFactory) {
            if (!defined('PULI_FACTORY_CLASS')) {
                throw new PuliUnavailableException('Puli Factory is not available');
            }

            $puliFactoryClass = PULI_FACTORY_CLASS;

            if (!ClassDiscovery::safeClassExists($puliFactoryClass)) {
                throw new PuliUnavailableException('Puli Factory class does not exist');
            }

            self::$puliFactory = new $puliFactoryClass();
        }

        return self::$puliFactory;
    }

    
    private static function getPuliDiscovery()
    {
        if (!isset(self::$puliDiscovery)) {
            $factory = self::getPuliFactory();
            $repository = $factory->createRepository();

            self::$puliDiscovery = $factory->createDiscovery($repository);
        }

        return self::$puliDiscovery;
    }

    public static function getCandidates($type)
    {
        $returnData = [];
        $bindings = self::getPuliDiscovery()->findBindings($type);

        foreach ($bindings as $binding) {
            $condition = true;
            if ($binding->hasParameterValue('depends')) {
                $condition = $binding->getParameterValue('depends');
            }
            $returnData[] = ['class' => $binding->getClassName(), 'condition' => $condition];
        }

        return $returnData;
    }
}
