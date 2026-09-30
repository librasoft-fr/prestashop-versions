<?php

namespace PsMcpServerDeps\Http\Discovery;

use Http\Client\HttpClient;
use PsMcpServerDeps\Http\Discovery\Exception\DiscoveryFailedException;

final class HttpClientDiscovery extends ClassDiscovery
{
    public static function find()
    {
        try {
            $client = static::findOneByType(HttpClient::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No HTTPlug clients found. Make sure to install a package providing "php-http/client-implementation". Example: "php-http/guzzle6-adapter".', 0, $e);
        }

        return static::instantiateClass($client);
    }
}
