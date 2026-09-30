<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Processor\ProcessorInterface;


interface ProcessableHandlerInterface
{
    
    public function pushProcessor(callable $callback): HandlerInterface;

    
    public function popProcessor(): callable;
}
