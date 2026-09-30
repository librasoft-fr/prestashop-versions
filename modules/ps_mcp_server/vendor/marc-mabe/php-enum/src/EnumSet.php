<?php

declare(strict_types=1);

namespace MabeEnum;

use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;


class EnumSet implements IteratorAggregate, Countable
{
    
    private $enumeration;

    
    private $enumerationCount;

    
    private $bitset = 0;

    
    private $emptyBitset = 0;

    
    
    private $fnDoGetIterator       = 'doGetIteratorInt';

    
    private $fnDoCount             = 'doCountInt';

    
    private $fnDoGetOrdinals       = 'doGetOrdinalsInt';

    
    private $fnDoGetBit            = 'doGetBitInt';

    
    private $fnDoSetBit            = 'doSetBitInt';

    
    private $fnDoUnsetBit          = 'doUnsetBitInt';

    
    private $fnDoGetBinaryBitsetLe = 'doGetBinaryBitsetLeInt';

    
    private $fnDoSetBinaryBitsetLe = 'doSetBinaryBitsetLeInt';
    

    
    public function __construct(string $enumeration, ?iterable $enumerators = null)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                '%s can handle subclasses of %s only',
                __METHOD__,
                Enum::class
            ));
        }

        $this->enumeration      = $enumeration;
        $this->enumerationCount = \count($enumeration::getConstants());

        
        
        
        if ($this->enumerationCount > \PHP_INT_SIZE * 8) {
            
            $this->bitset = $this->emptyBitset = \str_repeat("\0", (int)\ceil($this->enumerationCount / 8));

            
            $this->fnDoGetIterator       = 'doGetIteratorBin';
            $this->fnDoCount             = 'doCountBin';
            $this->fnDoGetOrdinals       = 'doGetOrdinalsBin';
            $this->fnDoGetBit            = 'doGetBitBin';
            $this->fnDoSetBit            = 'doSetBitBin';
            $this->fnDoUnsetBit          = 'doUnsetBitBin';
            $this->fnDoGetBinaryBitsetLe = 'doGetBinaryBitsetLeBin';
            $this->fnDoSetBinaryBitsetLe = 'doSetBinaryBitsetLeBin';
        }

        if ($enumerators !== null) {
            foreach ($enumerators as $enumerator) {
                $this->{$this->fnDoSetBit}($enumeration::get($enumerator)->getOrdinal());
            }
        }
    }

    
    public function __debugInfo() {
        $dbg = (array)$this;
        $dbg["\0" . self::class . "\0__enumerators"] = $this->getValues();
        return $dbg;
    }

    
    public function getEnumeration(): string
    {
        return $this->enumeration;
    }

    

    
    public function add($enumerator): void
    {
        $this->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    
    public function addIterable(iterable $enumerators): void
    {
        $bitset = $this->bitset;

        try {
            foreach ($enumerators as $enumerator) {
                $this->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
            }
        } catch (\Throwable $e) {
            
            $this->bitset = $bitset;
            throw $e;
        }
    }

    
    public function remove($enumerator): void
    {
        $this->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    
    public function attach($enumerator): void
    {
        $this->add($enumerator);
    }

    
    public function detach($enumerator): void
    {
        $this->remove($enumerator);
    }

    
    public function removeIterable(iterable $enumerators): void
    {
        $bitset = $this->bitset;

        try {
            foreach ($enumerators as $enumerator) {
                $this->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
            }
        } catch (\Throwable $e) {
            
            $this->bitset = $bitset;
            throw $e;
        }
    }

    
    public function setUnion(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset | $other->bitset;
    }

    
    public function setIntersect(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset & $other->bitset;
    }

    
    public function setDiff(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset & ~$other->bitset;
    }

    
    public function setSymDiff(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset ^ $other->bitset;
    }

    
    public function setBinaryBitsetLe(string $bitset): void
    {
        $this->{$this->fnDoSetBinaryBitsetLe}($bitset);
    }

    
    private function doSetBinaryBitsetLeBin($bitset): void
    {
        
        $thisBitset = $this->bitset;

        $size   = \strlen($thisBitset);
        $sizeIn = \strlen($bitset);

        if ($sizeIn < $size) {
            
            $bitset .= \str_repeat("\0", $size - $sizeIn);
        } elseif ($sizeIn > $size) {
            if (\ltrim(\substr($bitset, $size), "\0") !== '') {
                throw new InvalidArgumentException('out-of-range bits detected');
            }
            $bitset = \substr($bitset, 0, $size);
        }

        
        $lastByteMaxOrd = $this->enumerationCount % 8;
        if ($lastByteMaxOrd !== 0) {
            $lastByte         = $bitset[-1];
            $lastByteExpected = \chr((1 << $lastByteMaxOrd) - 1) & $lastByte;
            if ($lastByte !== $lastByteExpected) {
                throw new InvalidArgumentException('out-of-range bits detected');
            }

            $this->bitset = \substr($bitset, 0, -1) . $lastByteExpected;
        }

        $this->bitset = $bitset;
    }

    
    private function doSetBinaryBitsetLeInt($bitset): void
    {
        $len = \strlen($bitset);
        $int = 0;
        for ($i = 0; $i < $len; ++$i) {
            $ord = \ord($bitset[$i]);

            if ($ord && $i > \PHP_INT_SIZE - 1) {
                throw new InvalidArgumentException('out-of-range bits detected');
            }

            $int |= $ord << (8 * $i);
        }

        if ($int & (~0 << $this->enumerationCount)) {
            throw new InvalidArgumentException('out-of-range bits detected');
        }

        $this->bitset = $int;
    }

    
    public function setBinaryBitsetBe(string $bitset): void
    {
        $this->{$this->fnDoSetBinaryBitsetLe}(\strrev($bitset));
    }

    
    public function setBit(int $ordinal, bool $bit): void
    {
        if ($ordinal < 0 || $ordinal > $this->enumerationCount) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->enumerationCount}");
        }

        if ($bit) {
            $this->{$this->fnDoSetBit}($ordinal);
        } else {
            $this->{$this->fnDoUnsetBit}($ordinal);
        }
    }

    
    private function doSetBitBin($ordinal): void
    {
        
        $thisBitset = $this->bitset;

        $byte = (int) ($ordinal / 8);
        $thisBitset[$byte] = $thisBitset[$byte] | \chr(1 << ($ordinal % 8));

        $this->bitset = $thisBitset;
    }

    
    private function doSetBitInt($ordinal): void
    {
        
        $thisBitset = $this->bitset;

        $this->bitset = $thisBitset | (1 << $ordinal);
    }

    
    private function doUnsetBitBin($ordinal): void
    {
        
        $thisBitset = $this->bitset;

        $byte = (int) ($ordinal / 8);
        $thisBitset[$byte] = $thisBitset[$byte] & \chr(~(1 << ($ordinal % 8)));

        $this->bitset = $thisBitset;
    }

    
    private function doUnsetBitInt($ordinal): void
    {
        
        $thisBitset = $this->bitset;

        $this->bitset = $thisBitset & ~(1 << $ordinal);
    }

    

    
    public function with($enumerator): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        return $clone;
    }

    
    public function withIterable(iterable $enumerators): self
    {
        $clone = clone $this;
        foreach ($enumerators as $enumerator) {
            $clone->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
        return $clone;
    }

    
    public function without($enumerator): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        return $clone;
    }

    
    public function withoutIterable(iterable $enumerators): self
    {
        $clone = clone $this;
        foreach ($enumerators as $enumerator) {
            $clone->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
        return $clone;
    }

    
    public function withUnion(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setUnion($other);
        return $clone;
    }

    
    public function union(EnumSet $other): self
    {
        return $this->withUnion($other);
    }

    
    public function withIntersect(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setIntersect($other);
        return $clone;
    }

    
    public function intersect(EnumSet $other): self
    {
        return $this->withIntersect($other);
    }

    
    public function withDiff(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setDiff($other);
        return $clone;
    }

    
    public function diff(EnumSet $other): self
    {
        return $this->withDiff($other);
    }

    
    public function withSymDiff(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setSymDiff($other);
        return $clone;
    }

    
    public function symDiff(EnumSet $other): self
    {
        return $this->withSymDiff($other);
    }

    
    public function withBinaryBitsetLe(string $bitset): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoSetBinaryBitsetLe}($bitset);
        return $clone;
    }

    
    public function withBinaryBitsetBe(string $bitset): self
    {
        $clone = $this;
        $clone->{$this->fnDoSetBinaryBitsetLe}(\strrev($bitset));
        return $clone;
    }

    
    public function withBit(int $ordinal, bool $bit): self
    {
        $clone = clone $this;
        $clone->setBit($ordinal, $bit);
        return $clone;
    }

    

    
    public function has($enumerator): bool
    {
        return $this->{$this->fnDoGetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    
    public function contains($enumerator): bool
    {
        return $this->has($enumerator);
    }

    

    
    public function getIterator(): Iterator
    {
        return $this->{$this->fnDoGetIterator}();
    }

    
    private function doGetIteratorBin()
    {
        
        $bitset   = $this->bitset;
        $byteLen  = \strlen($bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($bitset[$bytePos] === "\0") {
                
                continue;
            }

            $ord = \ord($bitset[$bytePos]);
            for ($bitPos = 0; $bitPos < 8; ++$bitPos) {
                if ($ord & (1 << $bitPos)) {
                    $ordinal = $bytePos * 8 + $bitPos;
                    yield $ordinal => ($this->enumeration)::byOrdinal($ordinal);
                }
            }
        }
    }

    
    private function doGetIteratorInt()
    {
        
        $bitset = $this->bitset;
        $count  = $this->enumerationCount;
        for ($ordinal = 0; $ordinal < $count; ++$ordinal) {
            if ($bitset & (1 << $ordinal)) {
                yield $ordinal => ($this->enumeration)::byOrdinal($ordinal);
            }
        }
    }

    

    
    public function count(): int
    {
        return $this->{$this->fnDoCount}();
    }

    
    private function doCountBin()
    {
        
        $bitset  = $this->bitset;
        $count   = 0;
        $byteLen = \strlen($bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($bitset[$bytePos] === "\0") {
                
                continue;
            }

            $ord = \ord($bitset[$bytePos]);
            if ($ord & 0b00000001) ++$count;
            if ($ord & 0b00000010) ++$count;
            if ($ord & 0b00000100) ++$count;
            if ($ord & 0b00001000) ++$count;
            if ($ord & 0b00010000) ++$count;
            if ($ord & 0b00100000) ++$count;
            if ($ord & 0b01000000) ++$count;
            if ($ord & 0b10000000) ++$count;
        }
        return $count;
    }

    
    private function doCountInt()
    {
        
        $bitset = $this->bitset;
        $count  = 0;

        
        if ($bitset < 0) {
            $count  = 1;
            $bitset = $bitset & \PHP_INT_MAX;
        }

        
        $phpIntBitSize = \PHP_INT_SIZE * 8;
        for ($bitPos = 0; $bitPos < $phpIntBitSize; $bitPos += 8) {
            $bitChk = 0xff << $bitPos;
            $byte = $bitset & $bitChk;
            if ($byte) {
                $byte = $byte >> $bitPos;
                if ($byte & 0b00000001) ++$count;
                if ($byte & 0b00000010) ++$count;
                if ($byte & 0b00000100) ++$count;
                if ($byte & 0b00001000) ++$count;
                if ($byte & 0b00010000) ++$count;
                if ($byte & 0b00100000) ++$count;
                if ($byte & 0b01000000) ++$count;
                if ($byte & 0b10000000) ++$count;
            }

            if ($bitset <= $bitChk) {
                break;
            }
        }

        return $count;
    }

    
    public function isEqual(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && $this->bitset === $other->bitset;
    }

    
    public function isSubset(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && ($this->bitset & $other->bitset) === $this->bitset;
    }

    
    public function isSuperset(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && ($this->bitset | $other->bitset) === $this->bitset;
    }

    
    public function isEmpty(): bool
    {
        return $this->bitset === $this->emptyBitset;
    }

    
    public function getOrdinals(): array
    {
        return $this->{$this->fnDoGetOrdinals}();
    }

    
    private function doGetOrdinalsBin()
    {
        
        $bitset   = $this->bitset;
        $ordinals = [];
        $byteLen  = \strlen($bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($bitset[$bytePos] === "\0") {
                
                continue;
            }

            $ord = \ord($bitset[$bytePos]);
            for ($bitPos = 0; $bitPos < 8; ++$bitPos) {
                if ($ord & (1 << $bitPos)) {
                    $ordinals[] = $bytePos * 8 + $bitPos;
                }
            }
        }
        return $ordinals;
    }

    
    private function doGetOrdinalsInt()
    {
        
        $bitset   = $this->bitset;
        $ordinals = [];
        $count    = $this->enumerationCount;
        for ($ordinal = 0; $ordinal < $count; ++$ordinal) {
            if ($bitset & (1 << $ordinal)) {
                $ordinals[] = $ordinal;
            }
        }
        return $ordinals;
    }

    
    public function getValues(): array
    {
        $enumeration = $this->enumeration;
        $values      = [];
        foreach ($this->getOrdinals() as $ord) {
            $values[] = $enumeration::byOrdinal($ord)->getValue();
        }
        return $values;
    }

    
    public function getNames(): array
    {
        $enumeration = $this->enumeration;
        $names       = [];
        foreach ($this->getOrdinals() as $ord) {
            $names[] = $enumeration::byOrdinal($ord)->getName();
        }
        return $names;
    }

    
    public function getEnumerators(): array
    {
        $enumeration = $this->enumeration;
        $enumerators = [];
        foreach ($this->getOrdinals() as $ord) {
            $enumerators[] = $enumeration::byOrdinal($ord);
        }
        return $enumerators;
    }

    
    public function getBinaryBitsetLe(): string
    {
        return $this->{$this->fnDoGetBinaryBitsetLe}();
    }

    
    private function doGetBinaryBitsetLeBin()
    {
        
        $bitset = $this->bitset;

        return $bitset;
    }

    
    private function doGetBinaryBitsetLeInt()
    {
        $bin = \pack(\PHP_INT_SIZE === 8 ? 'P' : 'V', $this->bitset);
        return \substr($bin, 0, (int)\ceil($this->enumerationCount / 8));
    }

    
    public function getBinaryBitsetBe(): string
    {
        return \strrev($this->getBinaryBitsetLe());
    }

    
    public function getBit(int $ordinal): bool
    {
        if ($ordinal < 0 || $ordinal > $this->enumerationCount) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->enumerationCount}");
        }

        return $this->{$this->fnDoGetBit}($ordinal);
    }

    
    private function doGetBitBin($ordinal)
    {
        
        $bitset = $this->bitset;

        return (\ord($bitset[(int) ($ordinal / 8)]) & 1 << ($ordinal % 8)) !== 0;
    }

    
    private function doGetBitInt($ordinal)
    {
        
        $bitset = $this->bitset;

        return (bool)($bitset & (1 << $ordinal));
    }
}
