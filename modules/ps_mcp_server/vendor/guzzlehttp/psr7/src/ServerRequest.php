<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;


class ServerRequest extends Request implements ServerRequestInterface
{
    
    private $attributes = [];

    
    private $cookieParams = [];

    
    private $parsedBody;

    
    private $queryParams = [];

    
    private $serverParams;

    
    private $uploadedFiles = [];

    
    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile(
            $value['tmp_name'],
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    
    private static function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key] ?? null,
                'error' => $files['error'][$key] ?? null,
                'name' => $files['name'][$key] ?? null,
                'type' => $files['type'][$key] ?? null,
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    
    public static function fromGlobals(): ServerRequestInterface
    {
        $method = strtoupper(self::getServerParam('REQUEST_METHOD') ?? 'GET');
        $headers = self::removeInvalidHostHeader(self::getAllHeaders());
        $uri = self::getUriFromGlobals();
        $body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $serverProtocol = self::getServerParam('SERVER_PROTOCOL');
        $protocol = $serverProtocol !== null ? str_replace('HTTP/', '', $serverProtocol) : '1.1';

        $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $_SERVER);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    
    private static function getAllHeaders(): array
    {
        return self::normalizeHeaderValues(getallheaders());
    }

    
    private static function normalizeHeaderValues(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $normalized[$name] = (string) $value;
            }
        }

        return $normalized;
    }

    private static function getServerParam(string $key): ?string
    {
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    
    private static function removeInvalidHostHeader(array $headers): array
    {
        foreach ($headers as $name => $value) {
            if (strtolower((string) $name) !== 'host') {
                continue;
            }

            if (Rfc7230::parseHostHeader($value) === null) {
                unset($headers[$name]);
            }
        }

        return $headers;
    }

    
    private static function extractHostAndPortFromAuthority(string $authority): array
    {
        return Rfc7230::parseHostHeader($authority) ?? [null, null];
    }

    
    public static function getUriFromGlobals(): UriInterface
    {
        $uri = new Uri('');

        $https = self::getServerParam('HTTPS');
        $uri = $uri->withScheme(!empty($https) && $https !== 'off' ? 'https' : 'http');

        $hasPort = false;
        $authority = self::getServerParam('HTTP_HOST');
        if ($authority !== null) {
            [$host, $port] = self::extractHostAndPortFromAuthority($authority);
            if ($host !== null) {
                $uri = $uri->withHost($host);
            }

            if ($port !== null) {
                $hasPort = true;
                $uri = $uri->withPort($port);
            }
        } elseif (($serverName = self::getServerParam('SERVER_NAME')) !== null) {
            $uri = $uri->withHost($serverName);
        } elseif (($serverAddr = self::getServerParam('SERVER_ADDR')) !== null) {
            $uri = $uri->withHost($serverAddr);
        }

        $serverPort = self::getServerParam('SERVER_PORT');
        if (!$hasPort && $serverPort !== null && preg_match('/^[+-]?\d+$/', $serverPort) === 1) {
            $uri = $uri->withPort((int) $serverPort);
        }

        $hasQuery = false;
        $requestUri = self::getServerParam('REQUEST_URI');
        if ($requestUri !== null) {
            $requestUriParts = explode('?', $requestUri, 2);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        $queryString = self::getServerParam('QUERY_STRING');
        if (!$hasQuery && $queryString !== null) {
            $uri = $uri->withQuery($queryString);
        }

        return $uri;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $invalidUploadedFileFound = false;
        $invalidUploadedFile = null;
        $stack = [$uploadedFiles];

        while ($stack !== []) {
            foreach (\array_pop($stack) as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFileInterface) {
                    continue;
                }

                if (\is_array($uploadedFile)) {
                    $stack[] = $uploadedFile;
                    continue;
                }

                $invalidUploadedFileFound = true;
                $invalidUploadedFile = $uploadedFile;

                break 2;
            }
        }

        if ($invalidUploadedFileFound) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s inside ServerRequestInterface::withUploadedFiles() is deprecated; guzzlehttp/psr7 3.0 requires an UploadedFileInterface[] tree.',
                \get_debug_type($invalidUploadedFile)
            );
        }

        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        if ($data !== null && !\is_array($data) && !\is_object($data)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to ServerRequestInterface::withParsedBody() is deprecated; guzzlehttp/psr7 3.0 requires array|object|null.',
                \get_debug_type($data)
            );
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    
    public function getAttribute($attribute, $default = null)
    {
        if (!\is_string($attribute)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to ServerRequestInterface::getAttribute() is deprecated; guzzlehttp/psr7 3.0 requires string for $attribute.',
                \get_debug_type($attribute)
            );
        }

        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute($attribute, $value): ServerRequestInterface
    {
        if (!\is_string($attribute)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to ServerRequestInterface::withAttribute() is deprecated; guzzlehttp/psr7 3.0 requires string for $attribute.',
                \get_debug_type($attribute)
            );
        }

        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute($attribute): ServerRequestInterface
    {
        if (!\is_string($attribute)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to ServerRequestInterface::withoutAttribute() is deprecated; guzzlehttp/psr7 3.0 requires string for $attribute.',
                \get_debug_type($attribute)
            );
        }

        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }
}
