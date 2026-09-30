<?php


namespace Opis\JsonSchema;

use JsonSerializable;
use Opis\Uri\Uri as BaseUri;

class Uri extends BaseUri implements JsonSerializable
{
    
    protected static bool $useNormalizedComponents = false;

    public function __construct(array $components)
    {
        if (static::$useNormalizedComponents) {
            $components = self::normalizeComponents($components);
        }
        parent::__construct($components);
    }

    
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    
    public static function parse(string $uri, bool $ensure_fragment = false): ?self
    {
        if ($ensure_fragment && strpos($uri, '#') === false) {
            $uri .= '#';
        }

        return self::create($uri);
    }

    
    public static function merge($uri, $base, bool $ensure_fragment = false): ?self
    {
        $uri = self::resolveComponents($uri);

        if ($uri === null) {
            return null;
        }

        if ($ensure_fragment && !isset($uri['fragment'])) {
            $uri['fragment'] = '';
        }

        $base = self::resolveComponents($base);

        if (!$base) {
            return new self($uri);
        }

        return new self(self::mergeComponents($uri, $base));
    }

    
    public static function useNormalizedComponents(bool $value): void
    {
        self::$useNormalizedComponents = $value;
    }
}
