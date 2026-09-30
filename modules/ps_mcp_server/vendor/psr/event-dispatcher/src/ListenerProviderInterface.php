<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;


interface ListenerProviderInterface
{
    
    public function getListenersForEvent(object $event) : iterable;
}
