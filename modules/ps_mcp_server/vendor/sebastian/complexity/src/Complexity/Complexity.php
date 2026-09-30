<?php declare(strict_types=1);

namespace SebastianBergmann\Complexity;

use function str_contains;


final class Complexity
{
    
    private readonly string $name;

    
    private int $cyclomaticComplexity;

    
    public function __construct(string $name, int $cyclomaticComplexity)
    {
        $this->name                 = $name;
        $this->cyclomaticComplexity = $cyclomaticComplexity;
    }

    
    public function name(): string
    {
        return $this->name;
    }

    
    public function cyclomaticComplexity(): int
    {
        return $this->cyclomaticComplexity;
    }

    public function isFunction(): bool
    {
        return !$this->isMethod();
    }

    public function isMethod(): bool
    {
        return str_contains($this->name, '::');
    }
}
