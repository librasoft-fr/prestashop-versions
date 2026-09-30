<?php declare(strict_types=1);



namespace Monolog\Handler\FingersCrossed;

use Monolog\Logger;
use Psr\Log\LogLevel;


class ErrorLevelActivationStrategy implements ActivationStrategyInterface
{
    
    private $actionLevel;

    
    public function __construct($actionLevel)
    {
        $this->actionLevel = Logger::toMonologLevel($actionLevel);
    }

    public function isHandlerActivated(array $record): bool
    {
        return $record['level'] >= $this->actionLevel;
    }
}
