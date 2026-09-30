<?php declare(strict_types=1);

namespace SebastianBergmann\Diff;

use ArrayIterator;
use IteratorAggregate;
use Traversable;


final class Diff implements IteratorAggregate
{
    
    private string $from;

    
    private string $to;

    
    private array $chunks;

    
    public function __construct(string $from, string $to, array $chunks = [])
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->chunks = $chunks;
    }

    
    public function from(): string
    {
        return $this->from;
    }

    
    public function to(): string
    {
        return $this->to;
    }

    
    public function chunks(): array
    {
        return $this->chunks;
    }

    
    public function setChunks(array $chunks): void
    {
        $this->chunks = $chunks;
    }

    
    public function getFrom(): string
    {
        return $this->from;
    }

    
    public function getTo(): string
    {
        return $this->to;
    }

    
    public function getChunks(): array
    {
        return $this->chunks;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->chunks);
    }
}
