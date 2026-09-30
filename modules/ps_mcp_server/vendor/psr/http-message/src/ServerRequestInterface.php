<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface ServerRequestInterface extends RequestInterface
{
    
    public function getServerParams();

    
    public function getCookieParams();

    
    public function withCookieParams(array $cookies);

    
    public function getQueryParams();

    
    public function withQueryParams(array $query);

    
    public function getUploadedFiles();

    
    public function withUploadedFiles(array $uploadedFiles);

    
    public function getParsedBody();

    
    public function withParsedBody($data);

    
    public function getAttributes();

    
    public function getAttribute(string $name, $default = null);

    
    public function withAttribute(string $name, $value);

    
    public function withoutAttribute(string $name);
}
