<?php declare(strict_types=1);



namespace Monolog\Processor;


interface ProcessorInterface
{
    
    public function __invoke(array $record);
}
