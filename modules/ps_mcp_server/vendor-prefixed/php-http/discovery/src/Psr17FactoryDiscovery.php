<?php

namespace PsMcpServerDeps\Http\Discovery;

use PsMcpServerDeps\Http\Discovery\Exception\DiscoveryFailedException;
use PsMcpServerDeps\Http\Discovery\Exception\NotFoundException as RealNotFoundException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class Psr17FactoryDiscovery extends ClassDiscovery
{
    private static function createException($type, Exception $e)
    {
        return new RealNotFoundException(
            'No PSR-17 ' . $type . ' found. Install a package from this list: https://packagist.org/providers/psr/http-factory-implementation',
            0,
            $e
        );
    }

    public static function findRequestFactory()
    {
        try {
            $messageFactory = static::findOneByType(RequestFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('request factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findResponseFactory()
    {
        try {
            $messageFactory = static::findOneByType(ResponseFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('response factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findServerRequestFactory()
    {
        try {
            $messageFactory = static::findOneByType(ServerRequestFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('server request factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findStreamFactory()
    {
        try {
            $messageFactory = static::findOneByType(StreamFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('stream factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findUploadedFileFactory()
    {
        try {
            $messageFactory = static::findOneByType(UploadedFileFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('uploaded file factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findUriFactory()
    {
        try {
            $messageFactory = static::findOneByType(UriFactoryInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw self::createException('url factory', $e);
        }

        return static::instantiateClass($messageFactory);
    }

    public static function findUrlFactory()
    {
        return static::findUriFactory();
    }
}
