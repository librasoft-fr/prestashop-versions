<?php

namespace PsMcpServerDeps\Http\Discovery;

use Http\Client\HttpAsyncClient;
use PsMcpServerDeps\Http\Discovery\Exception\DiscoveryFailedException;

final class HttpAsyncClientDiscovery extends ClassDiscovery
{
    public static function find()
    {
        try {
            $asyncClient = static::findOneByType(HttpAsyncClient::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No HTTPlug async clients found. Make sure to install a package providing "php-http/async-client-implementation". Example: "php-http/guzzle6-adapter".', 0, $e);
        }

        return static::instantiateClass($asyncClient);
    }
}
