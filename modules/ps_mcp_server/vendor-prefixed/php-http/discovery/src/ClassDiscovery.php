<?php

namespace PsMcpServerDeps\Http\Discovery;

use PsMcpServerDeps\Http\Discovery\Exception\ClassInstantiationFailedException;
use PsMcpServerDeps\Http\Discovery\Exception\DiscoveryFailedException;
use PsMcpServerDeps\Http\Discovery\Exception\NoCandidateFoundException;
use PsMcpServerDeps\Http\Discovery\Exception\StrategyUnavailableException;

abstract class ClassDiscovery
{
    private static $strategies = [
        Strategy\GeneratedDiscoveryStrategy::class,
        Strategy\CommonClassesStrategy::class,
        Strategy\CommonPsr17ClassesStrategy::class,
        Strategy\PuliBetaStrategy::class,
    ];

    private static $deprecatedStrategies = [
        Strategy\PuliBetaStrategy::class => true,
    ];

    private static $cache = [];

    protected static function findOneByType($type)
    {
        if (null !== ($class = self::getFromCache($type))) {
            return $class;
        }

        static $skipStrategy;
        $skipStrategy ?? $skipStrategy = self::safeClassExists(Strategy\GeneratedDiscoveryStrategy::class) ? false : Strategy\GeneratedDiscoveryStrategy::class;

        $exceptions = [];
        foreach (self::$strategies as $strategy) {
            if ($skipStrategy === $strategy) {
                continue;
            }

            try {
                $candidates = $strategy::getCandidates($type);
            } catch (StrategyUnavailableException $e) {
                if (!isset(self::$deprecatedStrategies[$strategy])) {
                    $exceptions[] = $e;
                }

                continue;
            }

            foreach ($candidates as $candidate) {
                if (isset($candidate['condition'])) {
                    if (!self::evaluateCondition($candidate['condition'])) {
                        continue;
                    }
                }

                self::storeInCache($type, $candidate);

                return $candidate['class'];
            }

            $exceptions[] = new NoCandidateFoundException($strategy, $candidates);
        }

        throw DiscoveryFailedException::create($exceptions);
    }

    private static function getFromCache($type)
    {
        if (!isset(self::$cache[$type])) {
            return;
        }

        $candidate = self::$cache[$type];
        if (isset($candidate['condition'])) {
            if (!self::evaluateCondition($candidate['condition'])) {
                return;
            }
        }

        return $candidate['class'];
    }

    private static function storeInCache($type, $class)
    {
        self::$cache[$type] = $class;
    }

    public static function setStrategies(array $strategies)
    {
        self::$strategies = $strategies;
        self::clearCache();
    }

    public static function getStrategies(): iterable
    {
        return self::$strategies;
    }

    public static function appendStrategy($strategy)
    {
        self::$strategies[] = $strategy;
        self::clearCache();
    }

    public static function prependStrategy($strategy)
    {
        array_unshift(self::$strategies, $strategy);
        self::clearCache();
    }

    public static function clearCache()
    {
        self::$cache = [];
    }

    protected static function evaluateCondition($condition)
    {
        if (is_string($condition)) {
            return self::safeClassExists($condition);
        }
        if (is_callable($condition)) {
            return (bool) $condition();
        }
        if (is_bool($condition)) {
            return $condition;
        }
        if (is_array($condition)) {
            foreach ($condition as $c) {
                if (false === static::evaluateCondition($c)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    protected static function instantiateClass($class)
    {
        try {
            if (is_string($class)) {
                return new $class();
            }

            if (is_callable($class)) {
                return $class();
            }
        } catch (\Exception $e) {
            throw new ClassInstantiationFailedException('Unexpected exception when instantiating class.', 0, $e);
        }

        throw new ClassInstantiationFailedException('Could not instantiate class because parameter is neither a callable nor a string');
    }

    public static function safeClassExists($class)
    {
        try {
            return class_exists($class) || interface_exists($class);
        } catch (\Exception $e) {
            return false;
        }
    }
}
