<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Psr\Log\LogLevel;


class NullHandler extends Handler
{
    
    private $level;

    
    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    
    public function isHandling(array $record): bool
    {
        return $record['level'] >= $this->level;
    }

    
    public function handle(array $record): bool
    {
        return $record['level'] >= $this->level;
    }
}
