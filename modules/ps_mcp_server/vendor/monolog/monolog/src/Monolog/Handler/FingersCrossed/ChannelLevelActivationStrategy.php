<?php declare(strict_types=1);



namespace Monolog\Handler\FingersCrossed;

use Monolog\Logger;
use Psr\Log\LogLevel;


class ChannelLevelActivationStrategy implements ActivationStrategyInterface
{
    
    private $defaultActionLevel;

    
    private $channelToActionLevel;

    
    public function __construct($defaultActionLevel, array $channelToActionLevel = [])
    {
        $this->defaultActionLevel = Logger::toMonologLevel($defaultActionLevel);
        $this->channelToActionLevel = array_map('Monolog\Logger::toMonologLevel', $channelToActionLevel);
    }

    
    public function isHandlerActivated(array $record): bool
    {
        if (isset($this->channelToActionLevel[$record['channel']])) {
            return $record['level'] >= $this->channelToActionLevel[$record['channel']];
        }

        return $record['level'] >= $this->defaultActionLevel;
    }
}
