<?php declare(strict_types=1);



namespace Monolog\Formatter;

use DateTimeInterface;
use Monolog\LogRecord;


final class GoogleCloudLoggingFormatter extends JsonFormatter
{
    
    public function format(array $record): string
    {
        
        $record['severity'] = $record['level_name'];
        $record['time'] = $record['datetime']->format(DateTimeInterface::RFC3339_EXTENDED);

        
        unset($record['level'], $record['level_name'], $record['datetime']);

        return parent::format($record);
    }
}

