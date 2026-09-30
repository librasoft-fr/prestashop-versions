<?php

namespace PsMcpServerDeps\Http\Discovery\Strategy;

interface DiscoveryStrategy
{
    public static function getCandidates($type);
}
