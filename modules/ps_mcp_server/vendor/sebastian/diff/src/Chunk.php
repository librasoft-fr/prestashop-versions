<?php declare(strict_types=1);

namespace SebastianBergmann\Diff;

use ArrayIterator;
use IteratorAggregate;
use Traversable;


final class Chunk implements IteratorAggregate
{
    private int $start;
    private int $startRange;
    private int $end;
    private int $endRange;
    private array $lines;

    public function __construct(int $start = 0, int $startRange = 1, int $end = 0, int $endRange = 1, array $lines = [])
    {
        $this->start      = $start;
        $this->startRange = $startRange;
        $this->end        = $end;
        $this->endRange   = $endRange;
        $this->lines      = $lines;
    }

    public function start(): int
    {
        return $this->start;
    }

    public function startRange(): int
    {
        return $this->startRange;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function endRange(): int
    {
        return $this->endRange;
    }

    
    public function lines(): array
    {
        return $this->lines;
    }

    
    public function setLines(array $lines): void
    {
        foreach ($lines as $line) {
            if (!$line instanceof Line) {
                throw new InvalidArgumentException;
            }
        }

        $this->lines = $lines;
    }

    
    public function getStart(): int
    {
        return $this->start;
    }

    
    public function getStartRange(): int
    {
        return $this->startRange;
    }

    
    public function getEnd(): int
    {
        return $this->end;
    }

    
    public function getEndRange(): int
    {
        return $this->endRange;
    }

    
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->lines);
    }
}
