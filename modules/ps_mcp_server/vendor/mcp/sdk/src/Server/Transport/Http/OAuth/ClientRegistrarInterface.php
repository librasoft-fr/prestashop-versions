<?php



namespace Mcp\Server\Transport\Http\OAuth;

use Mcp\Exception\ClientRegistrationException;


interface ClientRegistrarInterface
{
    
    public function register(array $registrationRequest): array;
}
