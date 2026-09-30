<?php

namespace Http\Discovery\Strategy;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Mock\Client as Mock;


final class MockClientStrategy implements DiscoveryStrategy
{
    public static function getCandidates($type)
    {
        if (is_a(HttpClient::class, $type, true) || is_a(HttpAsyncClient::class, $type, true)) {
            return [['class' => Mock::class, 'condition' => Mock::class]];
        }

        return [];
    }
}
