<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Utils;


class SyslogHandler extends AbstractSyslogHandler
{
    
    protected $ident;
    
    protected $logopts;

    
    public function __construct(string $ident, $facility = LOG_USER, $level = Logger::DEBUG, bool $bubble = true, int $logopts = LOG_PID)
    {
        parent::__construct($facility, $level, $bubble);

        $this->ident = $ident;
        $this->logopts = $logopts;
    }

    
    public function close(): void
    {
        closelog();
    }

    
    protected function write(array $record): void
    {
        if (!openlog($this->ident, $this->logopts, $this->facility)) {
            throw new \LogicException('Can\'t open syslog for ident "'.$this->ident.'" and facility "'.$this->facility.'"' . Utils::getRecordMessageForException($record));
        }
        syslog($this->logLevels[$record['level']], (string) $record['formatted']);
    }
}
