<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface ResponseInterface extends MessageInterface
{
    
    public function getStatusCode();

    
    public function withStatus(int $code, string $reasonPhrase = '');

    
    public function getReasonPhrase();
}
