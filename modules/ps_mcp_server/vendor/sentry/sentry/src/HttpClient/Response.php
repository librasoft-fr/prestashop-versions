<?php

declare(strict_types=1);

namespace Sentry\HttpClient;

final class Response
{
    
    private $statusCode;

    
    private $headerNames = [];

    
    private $headers;

    
    private $error;

    
    public function __construct(int $statusCode, array $headers, string $error)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->error = $error;

        foreach ($headers as $name => $value) {
            $this->headerNames[strtolower($name)] = $name;
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode <= 299;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    
    public function getHeader(string $header): array
    {
        if (!$this->hasHeader($header)) {
            return [];
        }

        $header = $this->headerNames[strtolower($header)];

        return $this->headers[$header];
    }

    public function getHeaderLine(string $name): string
    {
        $value = $this->getHeader($name);
        if (empty($value)) {
            return '';
        }

        return implode(',', $value);
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error !== '';
    }
}
