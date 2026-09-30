<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Message
{
    
    public static function toString(MessageInterface $message): string
    {
        if ($message instanceof RequestInterface) {
            $msg = trim($message->getMethod().' '
                    .$message->getRequestTarget())
                .' HTTP/'.$message->getProtocolVersion();
            if (!$message->hasHeader('host')) {
                $msg .= "\r\nHost: ".$message->getUri()->getHost();
            }
        } elseif ($message instanceof ResponseInterface) {
            $msg = 'HTTP/'.$message->getProtocolVersion().' '
                .$message->getStatusCode().' '
                .$message->getReasonPhrase();
        } else {
            throw new \InvalidArgumentException('Unknown message type');
        }

        foreach ($message->getHeaders() as $name => $values) {
            if (is_string($name) && strtolower($name) === 'set-cookie') {
                foreach ($values as $value) {
                    $msg .= "\r\n{$name}: ".$value;
                }
            } else {
                $msg .= "\r\n{$name}: ".implode(', ', $values);
            }
        }

        return "{$msg}\r\n\r\n".$message->getBody();
    }

    
    public static function bodySummary(MessageInterface $message, int $truncateAt = 120): ?string
    {
        $body = $message->getBody();

        if (!$body->isSeekable() || !$body->isReadable()) {
            return null;
        }

        $size = $body->getSize();

        if ($size === 0) {
            return null;
        }

        $body->rewind();
        $summary = $body->read($truncateAt);

        if ($size > $truncateAt) {
            if (preg_match('//u', $summary) !== 1) {
                $summary = self::trimTrailingIncompleteUtf8Character($summary, $body->read(3));
            }

            $summary .= ' (truncated...)';
        }

        $body->rewind();

        
        
        if (preg_match('/[^\pL\pM\pN\pP\pS\pZ\n\r\t]/u', $summary) !== 0) {
            return null;
        }

        return $summary;
    }

    
    private static function trimTrailingIncompleteUtf8Character(string $summary, string $lookahead): string
    {
        $length = strlen($summary);

        if ($length === 0) {
            return $summary;
        }

        $start = $length - 1;

        while ($start >= 0) {
            $byte = ord($summary[$start]);

            if ($byte < 0x80 || $byte > 0xBF) {
                break;
            }

            --$start;
        }

        if ($start < 0) {
            return $summary;
        }

        $lead = ord($summary[$start]);

        if ($lead >= 0xC2 && $lead <= 0xDF) {
            $expectedLength = 2;
        } elseif ($lead >= 0xE0 && $lead <= 0xEF) {
            $expectedLength = 3;
        } elseif ($lead >= 0xF0 && $lead <= 0xF4) {
            $expectedLength = 4;
        } else {
            return $summary;
        }

        $availableLength = $length - $start;

        if ($availableLength >= $expectedLength) {
            return $summary;
        }

        $sequence = substr($summary, $start).substr($lookahead, 0, $expectedLength - $availableLength);

        if (strlen($sequence) !== $expectedLength || preg_match('//u', $sequence) !== 1) {
            return $summary;
        }

        return substr($summary, 0, $start);
    }

    
    public static function rewindBody(MessageInterface $message): void
    {
        $body = $message->getBody();

        if ($body->tell()) {
            $body->rewind();
        }
    }

    
    public static function parseMessage(string $message): array
    {
        if (!$message) {
            throw new \InvalidArgumentException('Invalid message');
        }

        $message = ltrim($message, "\r\n");

        $messageParts = preg_split("/\r?\n\r?\n/", $message, 2);

        if ($messageParts === false) {
            throw new \RuntimeException('Unable to split HTTP message: '.preg_last_error_msg());
        }

        if (count($messageParts) !== 2) {
            throw new \InvalidArgumentException('Invalid message: Missing header delimiter');
        }

        [$rawHeaders, $body] = $messageParts;
        $rawHeaders .= "\r\n"; 
        $headerParts = preg_split("/\r?\n/", $rawHeaders, 2);

        if ($headerParts === false) {
            throw new \RuntimeException('Unable to split HTTP message headers: '.preg_last_error_msg());
        }

        if (count($headerParts) !== 2) {
            throw new \InvalidArgumentException('Invalid message: Missing status line');
        }

        [$startLine, $rawHeaders] = $headerParts;

        $versionMatch = preg_match("/(?:^HTTP\/|^[A-Z]+ \S+ HTTP\/)(\d+(?:\.\d+)?)/i", $startLine, $matches);

        if ($versionMatch === false) {
            throw new \RuntimeException('Unable to parse HTTP start line: '.preg_last_error_msg());
        }

        if ($versionMatch === 1 && $matches[1] === '1.0') {
            
            $rawHeaders = preg_replace(Rfc7230::HEADER_FOLD_REGEX, ' ', $rawHeaders);

            if ($rawHeaders === null) {
                throw new \RuntimeException('Unable to unfold HTTP headers: '.preg_last_error_msg());
            }
        }

        
        $count = preg_match_all(Rfc7230::HEADER_REGEX, $rawHeaders, $headerLines, PREG_SET_ORDER);

        if ($count === false) {
            throw new \RuntimeException('Unable to parse HTTP headers: '.preg_last_error_msg());
        }

        
        if ($count !== substr_count($rawHeaders, "\n")) {
            
            $hasFoldedHeader = preg_match(Rfc7230::HEADER_FOLD_REGEX, $rawHeaders);

            if ($hasFoldedHeader === false) {
                throw new \RuntimeException('Unable to inspect HTTP header folding: '.preg_last_error_msg());
            }

            if ($hasFoldedHeader === 1) {
                throw new \InvalidArgumentException('Invalid header syntax: Obsolete line folding');
            }

            throw new \InvalidArgumentException('Invalid header syntax');
        }

        $headers = [];

        foreach ($headerLines as $headerLine) {
            $headers[$headerLine[1]][] = $headerLine[2];
        }

        return [
            'start-line' => $startLine,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    
    public static function parseRequestUri(string $path, array $headers): string
    {
        $host = self::getHostFromHeaders($headers);

        
        if ($host === null) {
            return $path;
        }

        $scheme = substr($host, -4) === ':443' ? 'https' : 'http';

        return $scheme.'://'.$host.'/'.ltrim($path, '/');
    }

    
    private static function getHostFromHeaders(array $headers): ?string
    {
        $hostKey = array_filter(array_keys($headers), function ($k) {
            
            $k = (string) $k;

            return strtolower($k) === 'host';
        });

        if (!$hostKey) {
            return null;
        }

        $host = $headers[reset($hostKey)][0];
        if (!is_string($host) || Rfc7230::parseHostHeader($host) === null) {
            throw new \InvalidArgumentException('Invalid request string');
        }

        return $host;
    }

    
    public static function parseRequest(string $message): RequestInterface
    {
        $data = self::parseMessage($message);
        if (strpbrk($data['start-line'], "\r\n") !== false) {
            throw new \InvalidArgumentException('Invalid request string');
        }

        $matches = [];
        $requestStartLineMatch = preg_match('/^[\S]+\s+([a-zA-Z]+:\/\/|\/).*/', $data['start-line'], $matches);

        if ($requestStartLineMatch === false) {
            throw new \RuntimeException('Unable to parse request start line: '.preg_last_error_msg());
        }

        if ($requestStartLineMatch === 0) {
            throw new \InvalidArgumentException('Invalid request string');
        }
        $parts = explode(' ', $data['start-line'], 3);
        $version = isset($parts[2]) ? explode('/', $parts[2])[1] : '1.1';

        $request = new Request(
            $parts[0],
            $matches[1] === '/' ? self::parseRequestUri($parts[1], $data['headers']) : $parts[1],
            $data['headers'],
            $data['body'],
            $version
        );

        return $matches[1] === '/' ? $request : $request->withRequestTarget($parts[1]);
    }

    
    public static function parseResponse(string $message): ResponseInterface
    {
        $data = self::parseMessage($message);
        if (strpbrk($data['start-line'], "\r\n") !== false) {
            throw new \InvalidArgumentException('Invalid response string');
        }

        
        
        
        $responseStartLineMatch = preg_match('/^HTTP\/.* [0-9]{3}( .*|$)/', $data['start-line']);

        if ($responseStartLineMatch === false) {
            throw new \RuntimeException('Unable to parse response start line: '.preg_last_error_msg());
        }

        if ($responseStartLineMatch === 0) {
            throw new \InvalidArgumentException('Invalid response string: '.$data['start-line']);
        }
        $parts = explode(' ', $data['start-line'], 3);

        return new Response(
            (int) $parts[1],
            $data['headers'],
            $data['body'],
            explode('/', $parts[0])[1],
            $parts[2] ?? null
        );
    }
}
