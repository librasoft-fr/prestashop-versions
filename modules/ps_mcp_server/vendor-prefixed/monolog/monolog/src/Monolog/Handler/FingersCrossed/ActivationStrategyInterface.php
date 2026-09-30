<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler\FingersCrossed;

interface ActivationStrategyInterface
{
    public function isHandlerActivated(array $record): bool;
}
