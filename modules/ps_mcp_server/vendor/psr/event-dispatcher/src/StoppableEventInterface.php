<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;


interface StoppableEventInterface
{
    
    public function isPropagationStopped() : bool;
}
