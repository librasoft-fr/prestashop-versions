<?php declare(strict_types=1);



namespace Monolog\Processor;


class MemoryPeakUsageProcessor extends MemoryProcessor
{
    
    public function __invoke(array $record): array
    {
        $usage = memory_get_peak_usage($this->realUsage);

        if ($this->useFormatting) {
            $usage = $this->formatBytes($usage);
        }

        $record['extra']['memory_peak_usage'] = $usage;

        return $record;
    }
}
