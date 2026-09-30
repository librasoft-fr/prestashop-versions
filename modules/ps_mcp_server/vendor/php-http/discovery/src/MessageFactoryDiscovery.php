<?php

namespace Http\Discovery;

use Http\Discovery\Exception\DiscoveryFailedException;
use Http\Message\MessageFactory;


final class MessageFactoryDiscovery extends ClassDiscovery
{
    
    public static function find()
    {
        try {
            $messageFactory = static::findOneByType(MessageFactory::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No php-http message factories found. Note that the php-http message factories are deprecated in favor of the PSR-17 message factories. To use the legacy Guzzle, Diactoros or Slim Framework factories of php-http, install php-http/message and php-http/message-factory and the chosen message implementation.', 0, $e);
        }

        return static::instantiateClass($messageFactory);
    }
}
