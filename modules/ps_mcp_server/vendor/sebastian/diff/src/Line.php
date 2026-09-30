<?php declare(strict_types=1);

namespace SebastianBergmann\Diff;

final class Line
{
    public const ADDED     = 1;
    public const REMOVED   = 2;
    public const UNCHANGED = 3;
    private int $type;
    private string $content;

    public function __construct(int $type = self::UNCHANGED, string $content = '')
    {
        $this->type    = $type;
        $this->content = $content;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function type(): int
    {
        return $this->type;
    }

    public function isAdded(): bool
    {
        return $this->type === self::ADDED;
    }

    public function isRemoved(): bool
    {
        return $this->type === self::REMOVED;
    }

    public function isUnchanged(): bool
    {
        return $this->type === self::UNCHANGED;
    }

    
    public function getContent(): string
    {
        return $this->content;
    }

    
    public function getType(): int
    {
        return $this->type;
    }
}
