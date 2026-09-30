<?php

declare(strict_types=1);



namespace JsonSchema\Uri\Retrievers;

use JsonSchema\Exception\ResourceNotFoundException;


class FileGetContents extends AbstractRetriever
{
    protected $messageBody;

    
    public function retrieve($uri)
    {
        if (function_exists('http_clear_last_response_headers')) {
            http_clear_last_response_headers();
        }

        $errorMessage = null;
        set_error_handler(function ($errno, $errstr) use (&$errorMessage) {
            $errorMessage = $errstr;
        });
        $response = file_get_contents($uri);
        restore_error_handler();

        if ($errorMessage) {
            throw new ResourceNotFoundException($errorMessage);
        }

        if (false === $response) {
            throw new ResourceNotFoundException('JSON schema not found at ' . $uri);
        }

        if ($response == ''
            && substr($uri, 0, 7) == 'file://' && substr($uri, -1) == '/'
        ) {
            throw new ResourceNotFoundException('JSON schema not found at ' . $uri);
        }

        $this->messageBody = $response;

        if (function_exists('http_get_last_response_headers')) {
            
            
            $httpResponseHeaders = http_get_last_response_headers();
        } else {
            
            $httpResponseHeaders = $http_response_header ?? [];
        }

        if (!empty($httpResponseHeaders)) {
            $this->fetchContentType($httpResponseHeaders);
        } else {
            $this->contentType = null;
        }

        return $this->messageBody;
    }

    
    private function fetchContentType(array $headers): bool
    {
        foreach (array_reverse($headers) as $header) {
            if ($this->contentType = self::getContentTypeMatchInHeader($header)) {
                return true;
            }
        }

        return false;
    }

    
    protected static function getContentTypeMatchInHeader($header)
    {
        if (0 < preg_match("/Content-Type:(\V*)/ims", $header, $match)) {
            return trim($match[1]);
        }

        return null;
    }
}
