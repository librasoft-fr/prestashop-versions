<?php

declare(strict_types=1);

namespace PsMcpServerDeps\GuzzleHttp\Psr7;

use Psr\Http\Message\UriInterface;

final class UriNormalizer
{
    public const PRESERVING_NORMALIZATIONS =
        self::CAPITALIZE_PERCENT_ENCODING |
        self::DECODE_UNRESERVED_CHARACTERS |
        self::CONVERT_EMPTY_PATH |
        self::REMOVE_DEFAULT_HOST |
        self::REMOVE_DEFAULT_PORT |
        self::REMOVE_DOT_SEGMENTS;

    public const CAPITALIZE_PERCENT_ENCODING = 1;

    public const DECODE_UNRESERVED_CHARACTERS = 2;

    public const CONVERT_EMPTY_PATH = 4;

    public const REMOVE_DEFAULT_HOST = 8;

    public const REMOVE_DEFAULT_PORT = 16;

    public const REMOVE_DOT_SEGMENTS = 32;

    public const REMOVE_DUPLICATE_SLASHES = 64;

    public const SORT_QUERY_PARAMETERS = 128;

    public static function normalize(UriInterface $uri, int $flags = self::PRESERVING_NORMALIZATIONS): UriInterface
    {
        if ($flags & self::CAPITALIZE_PERCENT_ENCODING) {
            $uri = self::capitalizePercentEncoding($uri);
        }

        if ($flags & self::DECODE_UNRESERVED_CHARACTERS) {
            $uri = self::decodeUnreservedCharacters($uri);
        }

        if ($flags & self::CONVERT_EMPTY_PATH && $uri->getPath() === ''
            && ($uri->getScheme() === 'http' || $uri->getScheme() === 'https')
        ) {
            $uri = $uri->withPath('/');
        }

        if ($flags & self::REMOVE_DEFAULT_HOST && $uri->getScheme() === 'file' && $uri->getHost() === 'localhost') {
            $uri = $uri->withHost('');
        }

        if ($flags & self::REMOVE_DEFAULT_PORT && $uri->getPort() !== null && Uri::isDefaultPort($uri)) {
            $uri = $uri->withPort(null);
        }

        if ($flags & self::REMOVE_DOT_SEGMENTS && !Uri::isRelativePathReference($uri)) {
            $uri = $uri->withPath(UriResolver::removeDotSegments($uri->getPath()));
        }

        if ($flags & self::REMOVE_DUPLICATE_SLASHES) {
            $path = preg_replace('#//++#', '/', $uri->getPath());

            if ($path === null) {
                throw new \RuntimeException('Unable to remove duplicate slashes from URI path: ' . preg_last_error_msg());
            }

            $uri = $uri->withPath($path);
        }

        if ($flags & self::SORT_QUERY_PARAMETERS && $uri->getQuery() !== '') {
            $queryKeyValues = explode('&', $uri->getQuery());
            sort($queryKeyValues);
            $uri = $uri->withQuery(implode('&', $queryKeyValues));
        }

        return $uri;
    }

    public static function isEquivalent(UriInterface $uri1, UriInterface $uri2, int $normalizations = self::PRESERVING_NORMALIZATIONS): bool
    {
        return (string) self::normalize($uri1, $normalizations) === (string) self::normalize($uri2, $normalizations);
    }

    private static function capitalizePercentEncoding(UriInterface $uri): UriInterface
    {
        $regex = '/(?:%[A-Fa-f0-9]{2})++/';

        $callback = function (array $match): string {
            return strtoupper($match[0]);
        };

        return $uri
            ->withPath(self::normalizePercentEncodingInComponent($uri->getPath(), $regex, $callback))
            ->withQuery(self::normalizePercentEncodingInComponent($uri->getQuery(), $regex, $callback))
            ->withFragment(self::normalizePercentEncodingInComponent($uri->getFragment(), $regex, $callback));
    }

    private static function decodeUnreservedCharacters(UriInterface $uri): UriInterface
    {
        $regex = '/%(?:2D|2E|5F|7E|3[0-9]|[46][1-9A-F]|[57][0-9A])/i';

        $callback = function (array $match): string {
            return rawurldecode($match[0]);
        };

        return $uri
            ->withPath(self::normalizePercentEncodingInComponent($uri->getPath(), $regex, $callback))
            ->withQuery(self::normalizePercentEncodingInComponent($uri->getQuery(), $regex, $callback))
            ->withFragment(self::normalizePercentEncodingInComponent($uri->getFragment(), $regex, $callback));
    }

    private static function normalizePercentEncodingInComponent(string $component, string $regex, callable $callback): string
    {
        $normalized = preg_replace_callback($regex, $callback, $component);

        if ($normalized === null) {
            throw new \RuntimeException('Unable to normalize URI component percent-encoding: ' . preg_last_error_msg());
        }

        return $normalized;
    }

    private function __construct()
    {
    }
}
