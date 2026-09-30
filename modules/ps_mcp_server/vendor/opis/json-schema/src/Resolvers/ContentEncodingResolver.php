<?php


namespace Opis\JsonSchema\Resolvers;

use Opis\JsonSchema\ContentEncoding;

class ContentEncodingResolver
{
    
    protected array $list;

    
    protected $defaultEncoding = null;

    
    public function __construct(array $list = [], $defaultEncoding = null)
    {
        $list += [
            'binary' => self::class . '::DecodeBinary',
            'base64' => self::class . '::DecodeBase64',
            'quoted-printable' => self::class . '::DecodeQuotedPrintable',
        ];

        $this->list = $list;
        $this->defaultEncoding = $defaultEncoding;
    }

    
    public function resolve(string $name)
    {
        return $this->list[$name] ?? $this->defaultEncoding;
    }

    
    public function register(string $name, ContentEncoding $encoding): self
    {
        $this->list[$name] = $encoding;

        return $this;
    }

    
    public function registerCallable(string $name, callable $encoding): self
    {
        $this->list[$name] = $encoding;

        return $this;
    }

    
    public function unregister(string $name): bool
    {
        if (isset($this->list[$name])) {
            unset($this->list[$name]);

            return true;
        }

        return false;
    }

    
    public function setDefaultHandler($handler): self
    {
        $this->defaultEncoding = $handler;
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'list' => $this->list,
            'defaultEncoding' => $this->defaultEncoding,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->list = $data['list'];
        $this->defaultEncoding = $data['defaultEncoding'] ?? null;
    }

    public static function DecodeBinary(string $value): ?string
    {
        return $value;
    }

    public static function DecodeBase64(string $value): ?string
    {
        $value = base64_decode($value, true);

        return is_string($value) ? $value : null;
    }

    public static function DecodeQuotedPrintable(string $value): ?string
    {
        return quoted_printable_decode($value);
    }
}