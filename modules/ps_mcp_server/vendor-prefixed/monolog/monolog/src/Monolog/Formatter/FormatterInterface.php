<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Formatter;

interface FormatterInterface
{
    public function format(array $record);

    public function formatBatch(array $records);
}
