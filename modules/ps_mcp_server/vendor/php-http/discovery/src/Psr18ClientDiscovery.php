<?php

namespace Http\Discovery;

use Http\Discovery\Exception\DiscoveryFailedException;
use Http\Discovery\Exception\NotFoundException as RealNotFoundException;
use Psr\Http\Client\ClientInterface;


final class Psr18ClientDiscovery extends ClassDiscovery
{
    
    public static function find()
    {
        try {
            $client = static::findOneByType(ClientInterface::class);
        } catch (DiscoveryFailedException $e) {
            throw new RealNotFoundException('No PSR-18 clients found. Make sure to install a package providing "psr/http-client-implementation". Example: "php-http/guzzle7-adapter".', 0, $e);
        }

        return static::instantiateClass($client);
    }
}
