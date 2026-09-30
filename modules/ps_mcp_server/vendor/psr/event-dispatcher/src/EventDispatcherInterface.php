<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;


interface EventDispatcherInterface
{
    
    public function dispatch(object $event);
}
