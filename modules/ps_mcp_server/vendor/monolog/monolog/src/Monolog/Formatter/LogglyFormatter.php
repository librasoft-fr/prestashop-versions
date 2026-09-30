<?php declare(strict_types=1);



namespace Monolog\Formatter;


class LogglyFormatter extends JsonFormatter
{
    
    public function __construct(int $batchMode = self::BATCH_MODE_NEWLINES, bool $appendNewline = false)
    {
        parent::__construct($batchMode, $appendNewline);
    }

    
    public function format(array $record): string
    {
        if (isset($record["datetime"]) && ($record["datetime"] instanceof \DateTimeInterface)) {
            $record["timestamp"] = $record["datetime"]->format("Y-m-d\TH:i:s.uO");
            unset($record["datetime"]);
        }

        return parent::format($record);
    }
}
