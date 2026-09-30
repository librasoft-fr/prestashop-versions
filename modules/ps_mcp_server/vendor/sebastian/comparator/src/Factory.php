<?php declare(strict_types=1);

namespace SebastianBergmann\Comparator;

use function array_unshift;

final class Factory
{
    private static ?Factory $instance = null;

    
    private array $customComparators = [];

    
    private array $defaultComparators = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self; 
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->registerDefaultComparators();
    }

    public function getComparatorFor(mixed $expected, mixed $actual): Comparator
    {
        foreach ($this->customComparators as $comparator) {
            if ($comparator->accepts($expected, $actual)) {
                return $comparator;
            }
        }

        foreach ($this->defaultComparators as $comparator) {
            if ($comparator->accepts($expected, $actual)) {
                return $comparator;
            }
        }

        throw new RuntimeException('No suitable Comparator implementation found');
    }

    
    public function register(Comparator $comparator): void
    {
        array_unshift($this->customComparators, $comparator);

        $comparator->setFactory($this);
    }

    
    public function unregister(Comparator $comparator): void
    {
        foreach ($this->customComparators as $key => $_comparator) {
            if ($comparator === $_comparator) {
                unset($this->customComparators[$key]);
            }
        }
    }

    public function reset(): void
    {
        $this->customComparators = [];
    }

    private function registerDefaultComparators(): void
    {
        $this->registerDefaultComparator(new MockObjectComparator);
        $this->registerDefaultComparator(new DateTimeComparator);
        $this->registerDefaultComparator(new DOMNodeComparator);
        $this->registerDefaultComparator(new SplObjectStorageComparator);
        $this->registerDefaultComparator(new ExceptionComparator);
        $this->registerDefaultComparator(new ObjectComparator);
        $this->registerDefaultComparator(new ResourceComparator);
        $this->registerDefaultComparator(new ArrayComparator);
        $this->registerDefaultComparator(new NumericComparator);
        $this->registerDefaultComparator(new ScalarComparator);
        $this->registerDefaultComparator(new TypeComparator);
    }

    private function registerDefaultComparator(Comparator $comparator): void
    {
        $this->defaultComparators[] = $comparator;

        $comparator->setFactory($this);
    }
}
