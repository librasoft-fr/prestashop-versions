<?php

namespace Http\Discovery;

use Http\Discovery\Exception\DiscoveryFailedException;
use Http\Message\StreamFactory;


final class StreamFactoryDiscovery extends ClassDiscovery
{
    
    public static function find()
    {
        try {
            $streamFactory = static::findOneByType(StreamFactory::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No stream factories found. To use Guzzle, Diactoros or Slim Framework factories install php-http/message and the chosen message implementation.', 0, $e);
        }

        return static::instantiateClass($streamFactory);
    }
}
