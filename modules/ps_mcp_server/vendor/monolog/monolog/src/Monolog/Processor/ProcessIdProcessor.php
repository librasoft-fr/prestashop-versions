<?php declare(strict_types=1);



namespace Monolog\Processor;


class ProcessIdProcessor implements ProcessorInterface
{
    
    public function __invoke(array $record): array
    {
        $record['extra']['process_id'] = getmypid();

        return $record;
    }
}
