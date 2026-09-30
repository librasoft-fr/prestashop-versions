<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler\FingersCrossed;

use PsMcpServerDeps\Monolog\Logger;

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
