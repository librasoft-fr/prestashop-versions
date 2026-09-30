<?php



namespace Symfony\Contracts\Service;

use Symfony\Contracts\Service\Attribute\SubscribedService;


interface ServiceSubscriberInterface
{
    
    public static function getSubscribedServices(): array;
}
