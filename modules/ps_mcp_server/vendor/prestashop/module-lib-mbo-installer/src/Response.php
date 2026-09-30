<?php

namespace Prestashop\ModuleLibMboInstaller;

class Response
{
    
    private $statusCode;
    
    private $body;
    
    private $headers;

    
    public function __construct($statusCode, $body, $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    
    public function getHeaders()
    {
        return $this->headers;
    }

    
    public function getBody()
    {
        return $this->body;
    }

    
    public function isSuccessful()
    {
        return substr((string) $this->statusCode, 0, 1) == '2';
    }
}
