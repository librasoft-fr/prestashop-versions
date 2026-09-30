<?php declare(strict_types=1);



namespace Monolog\Handler;

trait WebRequestRecognizerTrait
{
    
    protected function isWebRequest(): bool
    {
        return 'cli' !== \PHP_SAPI && 'phpdbg' !== \PHP_SAPI;
    }
}
