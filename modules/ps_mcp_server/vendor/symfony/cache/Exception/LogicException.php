<?php



namespace Symfony\Component\Cache\Exception;

use Psr\Cache\CacheException as Psr6CacheInterface;
use Psr\SimpleCache\CacheException as SimpleCacheInterface;

if (interface_exists(SimpleCacheInterface::class)) {
    class LogicException extends \LogicException implements Psr6CacheInterface, SimpleCacheInterface
    {
    }
} else {
    class LogicException extends \LogicException implements Psr6CacheInterface
    {
    }
}
