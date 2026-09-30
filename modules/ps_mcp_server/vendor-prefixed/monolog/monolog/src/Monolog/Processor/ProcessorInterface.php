<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Processor;

interface ProcessorInterface
{
    public function __invoke(array $record);
}
