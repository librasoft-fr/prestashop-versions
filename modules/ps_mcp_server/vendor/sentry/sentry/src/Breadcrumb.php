<?php

declare(strict_types=1);

namespace Sentry;


final class Breadcrumb
{
    
    public const TYPE_DEFAULT = 'default';

    
    public const TYPE_HTTP = 'http';

    
    public const TYPE_USER = 'user';

    
    public const TYPE_NAVIGATION = 'navigation';

    
    public const TYPE_ERROR = 'error';

    
    public const LEVEL_DEBUG = 'debug';

    
    public const LEVEL_INFO = 'info';

    
    public const LEVEL_WARNING = 'warning';

    
    public const LEVEL_ERROR = 'error';

    
    public const LEVEL_FATAL = 'fatal';

    
    private const ALLOWED_LEVELS = [
        self::LEVEL_DEBUG,
        self::LEVEL_INFO,
        self::LEVEL_WARNING,
        self::LEVEL_ERROR,
        self::LEVEL_FATAL,
    ];

    
    private $category;

    
    private $type;

    
    private $message;

    
    private $level;

    
    private $metadata;

    
    private $timestamp;

    
    public function __construct(string $level, string $type, string $category, ?string $message = null, array $metadata = [], ?float $timestamp = null)
    {
        if (!\in_array($level, self::ALLOWED_LEVELS, true)) {
            throw new \InvalidArgumentException('The value of the $level argument must be one of the Breadcrumb::LEVEL_* constants.');
        }

        $this->type = $type;
        $this->level = $level;
        $this->category = $category;
        $this->message = $message;
        $this->metadata = $metadata;
        $this->timestamp = $timestamp ?? microtime(true);
    }

    
    public function getType(): string
    {
        return $this->type;
    }

    
    public function withType(string $type): self
    {
        if ($type === $this->type) {
            return $this;
        }

        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    
    public function getLevel(): string
    {
        return $this->level;
    }

    
    public function withLevel(string $level): self
    {
        if (!\in_array($level, self::ALLOWED_LEVELS, true)) {
            throw new \InvalidArgumentException('The value of the $level argument must be one of the Breadcrumb::LEVEL_* constants.');
        }

        if ($level === $this->level) {
            return $this;
        }

        $new = clone $this;
        $new->level = $level;

        return $new;
    }

    
    public function getCategory(): string
    {
        return $this->category;
    }

    
    public function withCategory(string $category): self
    {
        if ($category === $this->category) {
            return $this;
        }

        $new = clone $this;
        $new->category = $category;

        return $new;
    }

    
    public function getMessage(): ?string
    {
        return $this->message;
    }

    
    public function withMessage(string $message): self
    {
        if ($message === $this->message) {
            return $this;
        }

        $new = clone $this;
        $new->message = $message;

        return $new;
    }

    
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    
    public function withMetadata(string $name, $value): self
    {
        if (isset($this->metadata[$name]) && $value === $this->metadata[$name]) {
            return $this;
        }

        $new = clone $this;
        $new->metadata[$name] = $value;

        return $new;
    }

    
    public function withoutMetadata(string $name): self
    {
        if (!isset($this->metadata[$name])) {
            return $this;
        }

        $new = clone $this;

        unset($new->metadata[$name]);

        return $new;
    }

    
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    
    public function withTimestamp(float $timestamp): self
    {
        if ($timestamp === $this->timestamp) {
            return $this;
        }

        $new = clone $this;
        $new->timestamp = $timestamp;

        return $new;
    }

    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['level'],
            $data['type'] ?? self::TYPE_DEFAULT,
            $data['category'],
            $data['message'] ?? null,
            $data['data'] ?? [],
            $data['timestamp'] ?? null
        );
    }
}
