<?php declare(strict_types=1);



namespace Monolog\Handler\FingersCrossed;


interface ActivationStrategyInterface
{
    
    public function isHandlerActivated(array $record): bool;
}
