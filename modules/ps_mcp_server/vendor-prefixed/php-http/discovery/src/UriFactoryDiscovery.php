<?php

namespace PsMcpServerDeps\Http\Discovery;

use Http\Message\UriFactory;
use PsMcpServerDeps\Http\Discovery\Exception\DiscoveryFailedException;

final class UriFactoryDiscovery extends ClassDiscovery
{
    public static function find()
    {
        try {
            $uriFactory = static::findOneByType(UriFactory::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No uri factories found. To use Guzzle, Diactoros or Slim Framework factories install php-http/message and the chosen message implementation.', 0, $e);
        }

        return static::instantiateClass($uriFactory);
    }
}
