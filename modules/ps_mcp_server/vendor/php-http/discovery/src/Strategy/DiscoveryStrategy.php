<?php

namespace Http\Discovery\Strategy;

use Http\Discovery\Exception\StrategyUnavailableException;


interface DiscoveryStrategy
{
    
    public static function getCandidates($type);
}
