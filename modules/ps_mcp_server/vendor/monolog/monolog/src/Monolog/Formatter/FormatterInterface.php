<?php declare(strict_types=1);



namespace Monolog\Formatter;


interface FormatterInterface
{
    
    public function format(array $record);

    
    public function formatBatch(array $records);
}
