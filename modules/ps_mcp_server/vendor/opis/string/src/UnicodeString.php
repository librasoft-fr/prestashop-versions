<?php


namespace Opis\String;

use RuntimeException;
use OutOfBoundsException;
use Countable, ArrayAccess;
use JsonSerializable;
use Opis\String\Exception\{
    UnicodeException,
    InvalidStringException,
    InvalidCodePointException
};

class UnicodeString implements Countable, ArrayAccess, JsonSerializable
{
    const KEEP_CASE = 0;

    const LOWER_CASE = 1;

    const UPPER_CASE = 2;

    const FOLD_CASE = 3;

    const ASCII_CONV = 4;

    
    private array $codes;

    
    private ?array $chars = null;
    private int $length;
    private ?string $str = null;
    private ?array $cache = null;

    
    private static array $maps = [];

    
    private function __construct(array $codes = [])
    {
        $this->codes = $codes;
        $this->length = count($codes);
    }

    
    public function codePoints(): array
    {
        return $this->codes;
    }

    
    public function chars(): array
    {
        if ($this->chars === null) {
            $this->chars = self::getCharsFromCodePoints($this->codes);
        }
        return $this->chars;
    }

    
    public function length(): int
    {
        return $this->length;
    }

    
    public function isEmpty(): bool
    {
        return $this->length === 0;
    }

    
    public function equals($text, bool $ignoreCase = false): bool
    {
        return $this->compareTo($text, $ignoreCase) === 0;
    }

    
    public function compareTo($text, bool $ignoreCase = false): int
    {
        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $text = self::resolveCodePoints($text, $mode);

        $length = count($text);

        if ($length !== $this->length) {
            return $this->length <=> $length;
        }

        return $this->getMappedCodes($mode) <=> $text;
    }

    
    public function contains($text, bool $ignoreCase = false): bool
    {
        return $this->indexOf($text, 0, $ignoreCase) !== -1;
    }

    
    public function startsWith($text, bool $ignoreCase = false): bool
    {
        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $text = self::resolveCodePoints($text, $mode);

        $len = count($text);

        if ($len === 0 || $len > $this->length) {
            return false;
        }

        return array_slice($this->getMappedCodes($mode), 0, $len) === $text;
    }

    
    public function endsWith($text, bool $ignoreCase = false): bool
    {
        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $text = self::resolveCodePoints($text, $mode);

        if (empty($text)) {
            return false;
        }

        $codes = $this->getMappedCodes($mode);

        $offset = $this->length - count($text);

        if ($offset < 0) {
            return false;
        }

        return array_slice($codes, $offset) === $text;
    }

    
    public function indexOf($text, int $offset = 0, bool $ignoreCase = false): int
    {
        if ($offset < 0) {
            $offset += $this->length;
        }
        if ($offset < 0 || $offset >= $this->length) {
            return -1;
        }

        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $text = self::resolveCodePoints($text, $mode);

        $len = count($text);

        if ($len === 0 || $offset + $len > $this->length) {
            return -1;
        }

        return $this->doIndexOf($this->getMappedCodes($mode), $text, $offset);
    }

    
    public function lastIndexOf($text, int $offset = 0, bool $ignoreCase = false): int
    {
        if ($offset < 0) {
            $start = $this->length + $offset;
            if ($start < 0) {
                return -1;
            }
            $last = 0;
        } else {
            if ($offset >= $this->length) {
                return -1;
            }
            $start = $this->length - 1;
            $last = $offset;
        }

        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $text = self::resolveCodePoints($text, $mode);

        $len = count($text);

        if ($len === 0) {
            return -1;
        }

        if ($offset < 0) {
            if ($len > $this->length) {
                return -1;
            }
            $start = min($start, $this->length - $len);
        } elseif ($offset + $len > $this->length) {
            return -1;
        }

        $codes = $this->getMappedCodes($mode);

        for ($i = $start; $i >= $last; $i--) {
            $match = true;

            for ($j = 0; $j < $len; $j++) {
                if ($codes[$i + $j] !== $text[$j]) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $i;
            }
        }

        return -1;
    }

    
    public function ensurePrefix($text, bool $ignoreCase = false, bool $allowPrefixOnly = true): self
    {
        $text = self::resolveCodePoints($text);

        $len = count($text);

        if ($len === 0) {
            return clone $this;
        }

        if ($this->length === 0) {
            return new static($text);
        }

        if ($ignoreCase) {
            $prefix = self::getMappedCodePoints($text, self::FOLD_CASE);
        } else {
            $prefix = &$text;
        }

        if ($this->length === $len) {
            $part = $this->getMappedCodes($ignoreCase ? self::FOLD_CASE : self::KEEP_CASE);
            if ($allowPrefixOnly && $part === $prefix) {
                return clone $this;
            }
            
            array_pop($part);
        } elseif ($this->length < $len) {
            $part = $this->getMappedCodes($ignoreCase ? self::FOLD_CASE : self::KEEP_CASE);
            
            if ($allowPrefixOnly && (array_slice($prefix, 0, $this->length) === $part)) {
                $text = array_slice($text, $this->length);
                return new static(array_merge($this->codes, $text));
            }
        } else {
            $part = array_slice($this->codes, 0, $len);
            if ($ignoreCase) {
                $part = self::getMappedCodePoints($part, self::FOLD_CASE);
            }
            if ($part === $prefix) {
                return clone $this;
            }
            
            array_pop($part);
        }

        $copy = $len;

        $part_len = count($part);

        while ($part_len) {
            if ($part === array_slice($prefix, -$part_len)) {
                $copy = $len - $part_len;
                break;
            }
            array_pop($part);
            $part_len--;
        }

        if ($copy === 0) {
            return clone $this;
        }

        if ($copy < $len) {
            $text = array_slice($text, 0, $copy);
        }

        return new static(array_merge($text, $this->codes));
    }

    
    public function ensureSuffix($text, bool $ignoreCase = false, bool $allowSuffixOnly = true): self
    {
        $text = self::resolveCodePoints($text);

        $len = count($text);

        if ($len === 0) {
            return clone $this;
        }

        if ($this->length === 0) {
            return new static($text);
        }

        if ($ignoreCase) {
            $suffix = self::getMappedCodePoints($text, self::FOLD_CASE);
        } else {
            $suffix = &$text;
        }

        if ($this->length === $len) {
            $part = $this->getMappedCodes($ignoreCase ? self::FOLD_CASE : self::KEEP_CASE);
            if ($allowSuffixOnly && $part === $suffix) {
                return clone $this;
            }
            
            array_shift($part);
        } elseif ($this->length < $len) {
            $part = $this->getMappedCodes($ignoreCase ? self::FOLD_CASE : self::KEEP_CASE);
            
            if ($allowSuffixOnly && (array_slice($suffix, -$this->length) === $part)) {
                $text = array_slice($text, 0, $len - $this->length);
                return new static(array_merge($text, $this->codes));
            }
        } else {
            $part = array_slice($this->codes, -$len);
            if ($ignoreCase) {
                $part = self::getMappedCodePoints($part, self::FOLD_CASE);
            }
            if ($part === $suffix) {
                return clone $this;
            }
            
            array_shift($part);
        }

        $skip = 0;

        $part_len = count($part);

        while ($part_len) {
            if ($part === array_slice($suffix, 0, $part_len)) {
                $skip = $part_len;
                break;
            }
            array_shift($part);
            $part_len--;
        }

        if ($skip === $len) {
            return clone $this;
        }

        if ($skip) {
            array_splice($text, 0, $skip);
        }

        return new static(array_merge($this->codes, $text));
    }

    
    public function append($text, int $mode = self::KEEP_CASE): self
    {
        return new static(array_merge($this->codes, self::resolveCodePoints($text, $mode)));
    }

    
    public function prepend($text, int $mode = self::KEEP_CASE): self
    {
        return new static(array_merge(self::resolveCodePoints($text, $mode), $this->codes));
    }

    
    public function insert($text, int $offset, int $mode = self::KEEP_CASE): self
    {
        $codes = $this->codes;

        array_splice($codes, $offset, 0, self::resolveCodePoints($text, $mode));

        return new static($codes);
    }

    
    public function remove(int $offset, ?int $length = null): self
    {
        $codes = $this->codes;

        if ($length === null) {
            array_splice($codes, $offset);
        } else {
            array_splice($codes, $offset, $length);
        }

        return new static($codes);
    }

    
    public function trim($mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($mask, true, true);
    }

    
    public function trimLeft($mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($mask, true, false);
    }

    
    public function trimRight($mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($mask, false, true);
    }

    
    public function reverse(): self
    {
        return new static(array_reverse($this->codes));
    }

    
    public function repeat(int $times = 1): self
    {
        if ($times <= 1) {
            return clone $this;
        }

        $codes = [];

        while ($times--) {
            $codes = array_merge($codes, $this->codes);
        }

        return new static($codes);
    }

    
    public function replace($subject, $replace, int $offset = 0, bool $ignoreCase = false): self
    {
        if ($offset < 0) {
            $offset += $this->length;
        }
        if ($offset < 0 || $offset >= $this->length) {
            return clone $this;
        }

        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $subject = self::resolveCodePoints($subject, $mode);

        $len = count($subject);

        if ($len === 0 || $offset + $len > $this->length) {
            return clone $this;
        }

        $offset = $this->doIndexOf($this->getMappedCodes($mode), $subject, $offset);

        if ($offset === -1) {
            return clone $this;
        }

        $codes = $this->codes;

        array_splice($codes, $offset, count($subject), self::resolveCodePoints($replace));

        return new static($codes);
    }

    
    public function replaceAll($subject, $replace, int $offset = 0, bool $ignoreCase = false): self
    {
        if ($offset < 0) {
            $offset += $this->length;
        }
        if ($offset < 0 || $offset >= $this->length) {
            return clone $this;
        }

        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;

        $subject = self::resolveCodePoints($subject, $mode);

        $len = count($subject);

        if ($len === 0 || $offset + $len > $this->length) {
            return clone $this;
        }

        $replace = self::resolveCodePoints($replace);

        $codes = $this->getMappedCodes($mode);

        $copy = $this->codes;

        $fix = count($replace) - $len;

        $t = 0;

        while (($pos = $this->doIndexOf($codes, $subject, $offset)) >= 0) {
            array_splice($copy, $pos + $t * $fix, $len, $replace);
            $offset = $pos + $len;
            $t++;
        }

        return new static($copy);
    }

    
    public function split($delimiter = '', bool $ignoreCase = false): array
    {
        $mode = $ignoreCase ? self::FOLD_CASE : self::KEEP_CASE;
        $delimiter = self::resolveCodePoints($delimiter, $mode);
        $len = count($delimiter);

        $ret = [];

        if ($len === 0) {
            foreach ($this->codes as $code) {
                $ret[] = new static([$code]);
            }
        } else {
            $codes = $this->getMappedCodes($mode);

            $offset = 0;

            while (($pos = $this->doIndexOf($codes, $delimiter, $offset)) >= 0) {
                $ret[] = new static(array_slice($this->codes, $offset, $pos - $offset));
                $offset = $pos + $len;
            }

            $ret[] = new static(array_slice($this->codes, $offset));
        }

        return $ret;
    }

    
    public function substring(int $start, ?int $length = null): self
    {
        return new static(array_slice($this->codes, $start, $length));
    }

    
    public function pad(int $size, $char = 0x20): self
    {
        return new static(array_pad($this->codes, $size, self::resolveFirstCodePoint($char, 0x20)));
    }

    
    public function padLeft(int $size, $char = 0x20): self
    {
        if ($size > 0) {
            $size = -$size;
        }

        return $this->pad($size, $char);
    }

    
    public function padRight(int $size, $char = 0x20): self
    {
        if ($size < 0) {
            $size = -$size;
        }

        return $this->pad($size, $char);
    }

    
    public function isLowerCase(): bool
    {
        return $this->isCase(self::LOWER_CASE);
    }

    
    public function isUpperCase(): bool
    {
        return $this->isCase(self::UPPER_CASE);
    }

    
    public function isAscii(): bool
    {
        $key = 'i' . self::ASCII_CONV;

        if (!isset($this->cache[$key])) {
            $ok = true;

            foreach ($this->codes as $code) {
                if ($code >= 0x80) {
                    $ok = false;
                    break;
                }
            }

            $this->cache[$key] = $ok;
        }

        return $this->cache[$key];
    }

    
    public function toLower(): self
    {
        if ($this->cache['i' . self::LOWER_CASE] ?? false) {
            return clone $this;
        }
        return new static($this->getMappedCodes(self::LOWER_CASE));
    }

    
    public function toUpper(): self
    {
        if ($this->cache['i' . self::UPPER_CASE] ?? false) {
            return clone $this;
        }
        return new static($this->getMappedCodes(self::UPPER_CASE));
    }

    
    public function toAscii(): self
    {
        if ($this->cache['i' . self::ASCII_CONV] ?? false) {
            return clone $this;
        }
        return new static($this->getMappedCodes(self::ASCII_CONV));
    }

    
    public function charAt(int $index): string
    {
        
        if ($index < 0 && $index + $this->length >= 0) {
            $index += $this->length;
        }

        if ($index < 0 || $index >= $this->length) {
            return '';
        }

        return $this->chars()[$index];
    }

    
    public function codePointAt(int $index): int
    {
        
        if ($index < 0 && $index + $this->length >= 0) {
            $index += $this->length;
        }

        if ($index < 0 || $index >= $this->length) {
            return -1;
        }

        return $this->codes[$index];
    }

    
    public function __invoke(int $offset): int
    {
        if ($offset < 0) {
            if ($offset + $this->length < 0) {
                throw new OutOfBoundsException("Undefined offset: {$offset}");
            }
            $offset += $this->length;
        } elseif ($offset >= $this->length) {
            throw new OutOfBoundsException("Undefined offset: {$offset}");
        }

        return $this->codes[$offset];
    }

    
    public function offsetExists($offset): bool
    {
        
        if ($offset < 0) {
            $offset += $this->length;
        }

        return isset($this->codes[$offset]);
    }

    
    public function offsetGet($offset): string
    {
        if ($offset < 0) {
            if ($offset + $this->length < 0) {
                throw new OutOfBoundsException("Undefined offset: {$offset}");
            }
            $offset += $this->length;
        } elseif ($offset >= $this->length) {
            throw new OutOfBoundsException("Undefined offset: {$offset}");
        }

        return $this->chars()[$offset];
    }

    
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        
        if ($offset < 0) {
            $offset += $this->length;
        }

        if (!isset($this->codes[$offset])) {
            return;
        }


        $value = self::resolveFirstCodePoint($value);
        if ($value === -1) {
            return;
        }

        if ($value === $this->codes[$offset]) {
            
            return;
        }

        $this->codes[$offset] = $value;

        
        $this->str = null;
        $this->cache = null;
        if ($this->chars) {
            $this->chars[$offset] = self::getCharFromCodePoint($value);
        }
    }

    
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new RuntimeException("Invalid operation");
    }

    
    public function count(): int
    {
        return $this->length;
    }

    
    public function __toString(): string
    {
        if ($this->str === null) {
            $this->str = self::getStringFromCodePoints($this->codes);
        }

        return $this->str;
    }

    
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    public function __serialize(): array
    {
        return [
            'value' => $this->__toString(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->str = $data['value'];
        $this->codes = self::getCodePointsFromString($this->str);
        $this->length = count($this->codes);
    }

    
    public static function from(string $string, ?string $encoding = null, int $mode = self::KEEP_CASE): self
    {
        if ($encoding !== null && strcasecmp($encoding, 'UTF-8') !== 0) {
            if (false === $string = @iconv($encoding, 'UTF-8', $string)) {
                throw new UnicodeException("Could not convert string from '$encoding' encoding to UTF-8 encoding");
            }
        }

        $instance = new static(self::getCodePointsFromString($string, $mode));
        if ($mode === self::KEEP_CASE) {
            $instance->str = $string;
        }
        return $instance;
    }

    
    public static function fromCodePoints(array $codes, int $mode = self::KEEP_CASE): self
    {
        $map = self::getMapByMode($mode);

        foreach ($codes as &$code) {
            if (!is_int($codes) || !self::isValidCodePoint($code)) {
                throw new InvalidCodePointException($code);
            } else {
                $code = $map[$code] ?? $code;
            }
        }

        return new static(array_values($codes));
    }

    
    public static function getCharFromCodePoint(int $code): string
    {
        if ($code < 0) {
            return '';
        }

        if ($code < 0x80) {
            return chr($code);
        }

        if ($code < 0x800) {
            return chr(($code >> 6) + 0xC0) . chr(($code & 0x3F) + 0x80);
        }

        if ($code >= 0xD800 && $code <= 0xDFFF) {
            
            return '';
        }

        if ($code <= 0xFFFF) {
            return
                chr(($code >> 12) + 0xE0) .
                chr((($code >> 6) & 0x3F) + 0x80) .
                chr(($code & 0x3F) + 0x80);
        }

        if ($code <= 0x10FFFF) {
            return
                chr(($code >> 18) + 0xF0) .
                chr((($code >> 12) & 0x3F) + 0x80) .
                chr((($code >> 6) & 0x3F) + 0x80) .
                chr(($code & 0x3F) + 0x80);
        }

        

        return '';
    }

    
    public static function getCodePointsFromString(string $str, int $mode = self::KEEP_CASE): array
    {
        
        
        
        
        
        
        
        
        

        $codes = [];
        $length = strlen($str);
        $mode = self::getMapByMode($mode);

        $i = 0;
        while ($i < $length) {
            $ord0 = ord($str[$i++]);

            if ($ord0 < 0x80) {
                $codes[] = $mode[$ord0] ?? $ord0;
                continue;
            }

            if ($i === $length || $ord0 < 0xC2 || $ord0 > 0xF4) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord1 = ord($str[$i++]);

            if ($ord0 < 0xE0) {
                if ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                $ord1 = ($ord0 - 0xC0) * 64 + $ord1 - 0x80;
                $codes[] = $mode[$ord1] ?? $ord1;

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord2 = ord($str[$i++]);

            if ($ord0 < 0xF0) {
                if ($ord0 === 0xE0) {
                    if ($ord1 < 0xA0 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord0 === 0xED) {
                    if ($ord1 < 0x80 || $ord1 >= 0xA0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                $ord2 = ($ord0 - 0xE0) * 0x1000 + ($ord1 - 0x80) * 64 + $ord2 - 0x80;
                $codes[] = $mode[$ord2] ?? $ord2;

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord3 = ord($str[$i++]);

            if ($ord0 < 0xF5) {
                if ($ord0 === 0xF0) {
                    if ($ord1 < 0x90 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord0 === 0xF4) {
                    if ($ord1 < 0x80 || $ord1 >= 0x90) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 3);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord3 < 0x80 || $ord3 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                $ord3 = ($ord0 - 0xF0) * 0x40000 + ($ord1 - 0x80) * 0x1000 + ($ord2 - 0x80) * 64 + $ord3 - 0x80;
                $codes[] = $mode[$ord3] ?? $ord3;

                continue;
            }

            throw new InvalidStringException($str, $i - 1);
        }

        return $codes;
    }

    
    public static function walkString(string $str): iterable
    {
        $i = 0;
        $length = strlen($str);

        while ($i < $length) {
            $index = $i;

            $ord0 = ord($str[$i++]);

            if ($ord0 < 0x80) {
                yield $index => [
                    $ord0,
                    [$ord0]
                ];
                continue;
            }

            if ($i === $length || $ord0 < 0xC2 || $ord0 > 0xF4) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord1 = ord($str[$i++]);

            if ($ord0 < 0xE0) {
                if ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                yield $index => [
                    ($ord0 - 0xC0) * 64 + $ord1 - 0x80,
                    [$ord0, $ord1]
                ];

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord2 = ord($str[$i++]);

            if ($ord0 < 0xF0) {
                if ($ord0 === 0xE0) {
                    if ($ord1 < 0xA0 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord0 === 0xED) {
                    if ($ord1 < 0x80 || $ord1 >= 0xA0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                yield $index => [
                    ($ord0 - 0xE0) * 0x1000 + ($ord1 - 0x80) * 64 + $ord2 - 0x80,
                    [$ord0, $ord1, $ord2]
                ];

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord3 = ord($str[$i++]);

            if ($ord0 < 0xF5) {
                if ($ord0 === 0xF0) {
                    if ($ord1 < 0x90 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord0 === 0xF4) {
                    if ($ord1 < 0x80 || $ord1 >= 0x90) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 3);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord3 < 0x80 || $ord3 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                yield $index => [
                    ($ord0 - 0xF0) * 0x40000 + ($ord1 - 0x80) * 0x1000 + ($ord2 - 0x80) * 64 + $ord3 - 0x80,
                    [$ord0, $ord1, $ord2, $ord3]
                ];

                continue;
            }

            throw new InvalidStringException($str, $i - 1);
        }
    }

    
    public static function getStringLength(string $str): int
    {
        $count = 0;
        $length = strlen($str);

        $i = 0;
        while ($i < $length) {
            $ord0 = ord($str[$i++]);

            if ($ord0 < 0x80) {
                $count++;
                continue;
            }

            if ($i === $length || $ord0 < 0xC2 || $ord0 > 0xF4) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord1 = ord($str[$i++]);

            if ($ord0 < 0xE0) {
                if ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                
                $count++;

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord2 = ord($str[$i++]);

            if ($ord0 < 0xF0) {
                if ($ord0 === 0xE0) {
                    if ($ord1 < 0xA0 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord0 === 0xED) {
                    if ($ord1 < 0x80 || $ord1 >= 0xA0) {
                        throw new InvalidStringException($str, $i - 2);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                
                $count++;

                continue;
            }

            if ($i === $length) {
                throw new InvalidStringException($str, $i - 1);
            }

            $ord3 = ord($str[$i++]);

            if ($ord0 < 0xF5) {
                if ($ord0 === 0xF0) {
                    if ($ord1 < 0x90 || $ord1 >= 0xC0) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord0 === 0xF4) {
                    if ($ord1 < 0x80 || $ord1 >= 0x90) {
                        throw new InvalidStringException($str, $i - 3);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 3);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 2);
                }

                if ($ord3 < 0x80 || $ord3 >= 0xC0) {
                    throw new InvalidStringException($str, $i - 1);
                }

                
                $count++;

                continue;
            }

            throw new InvalidStringException($str, $i - 1);
        }

        return $count;
    }

    
    public static function getCharsFromCodePoints(array $codes, int $mode = self::KEEP_CASE): array
    {
        $mode = self::getMapByMode($mode);

        foreach ($codes as &$code) {
            $char = self::getCharFromCodePoint($mode[$code] ?? $code);
            if ($char === '') {
                throw new InvalidCodePointException($code);
            } else {
                $code = $char;
            }
        }

        return $codes;
    }

    
    public static function getCharsFromString(string $str, int $mode = self::KEEP_CASE): array
    {
        return self::getCharsFromCodePoints(self::getCodePointsFromString($str), $mode);
    }

    
    public static function getStringFromCodePoints(array $codes, int $mode = self::KEEP_CASE): string
    {
        $str = '';

        $mode = self::getMapByMode($mode);

        foreach ($codes as $code) {
            if (isset($mode[$code])) {
                $code = $mode[$code];
            }

            if ($code < 0x80) {
                $str .= chr($code);
                continue;
            }

            if ($code < 0x800) {
                $str .= chr(($code >> 6) + 0xC0) . chr(($code & 0x3F) + 0x80);
                continue;
            }

            if ($code >= 0xD800 && $code <= 0xDFFF) {
                continue;
            }

            if ($code <= 0xFFFF) {
                $str .=
                    chr(($code >> 12) + 0xE0) .
                    chr((($code >> 6) & 0x3F) + 0x80) .
                    chr(($code & 0x3F) + 0x80);
                continue;
            }

            if ($code <= 0x10FFFF) {
                $str .=
                    chr(($code >> 18) + 0xF0) .
                    chr((($code >> 12) & 0x3F) + 0x80) .
                    chr((($code >> 6) & 0x3F) + 0x80) .
                    chr(($code & 0x3F) + 0x80);
            }
        }

        return $str;
    }

    
    public static function getMappedCodePoints(array $codes, int $mode): array
    {
        if ($mode === self::KEEP_CASE) {
            return $codes;
        }

        $mode = self::getMapByMode($mode);

        if (empty($mode)) {
            return $codes;
        }

        foreach ($codes as &$code) {
            $code = $mode[$code] ?? $code;
        }

        return $codes;
    }

    
    public static function isValidCodePoint(int $code): bool
    {
        if ($code < 0 || $code > 0x10FFFF) {
            return false;
        }

        return $code < 0xD800 || $code > 0xDFFF;
    }

    
    private function getMappedCodes(int $mode): array
    {
        if ($mode === self::KEEP_CASE || ($this->cache['i' . $mode] ?? false)) {
            return $this->codes;
        }

        $key = 'm' . $mode;

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = self::getMappedCodePoints($this->codes, $mode);
        }

        return $this->cache[$key];
    }

    
    private function isCase(int $mode): bool
    {
        $key = 'i' . $mode;

        if (!isset($this->cache[$key])) {
            $list = self::getMapByMode($mode);
            foreach ($this->codes as $code) {
                if (isset($list[$code])) {
                    return $this->cache[$key] = false;
                }
            }

            return $this->cache[$key] = true;
        }

        return $this->cache[$key];
    }

    
    private function doIndexOf(array $codes, array $text, int $offset = 0): int
    {
        $len = count($text);

        for ($i = $offset, $last = count($codes) - $len; $i <= $last; $i++) {
            $match = true;

            for ($j = 0; $j < $len; $j++) {
                if ($codes[$i + $j] !== $text[$j]) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $i;
            }
        }

        return -1;
    }

    
    private function doTrim($mask, bool $left, bool $right): self
    {
        if ($this->length === 0) {
            return clone $this;
        }

        $mask = self::resolveCodePoints($mask);

        if (empty($mask)) {
            return clone $this;
        }

        $codes = $this->codes;

        if ($left) {
            while (in_array($codes[0], $mask, true)) {
                array_shift($codes);
                if (empty($codes)) {
                    return new static();
                }
            }
        }

        if ($right) {
            $last = count($codes) - 1;
            while (in_array($codes[$last], $mask, true)) {
                array_pop($codes);
                if (--$last < 0) {
                    return new static();
                }
            }
        }

        return new static($codes);
    }


    
    private static function resolveCodePoints($text, int $mode = self::KEEP_CASE): array
    {
        if ($text instanceof self) {
            return $text->getMappedCodes($mode);
        }

        if (is_string($text)) {
            return self::getCodePointsFromString($text, $mode);
        }

        if ($text && is_array($text) && is_int($text[0])) {
            
            return self::getMappedCodePoints($text, $mode);
        }

        return [];
    }

    
    private static function resolveFirstCodePoint($text, int $invalid = -1): int
    {
        if ($text instanceof self) {
            return $text->length === 0 ? $invalid : $text->codes[0];
        }

        if (is_array($text)) {
            if (empty($text)) {
                return $invalid;
            }
            $text = reset($text);
        }

        if (is_string($text)) {
            if (isset($text[4])) {
                $text = substr($text, 0, 4);
            }
            return self::getCodePointsFromString($text)[0] ?? $invalid;
        }

        if (is_int($text)) {
            return self::isValidCodePoint($text) ? $text : $invalid;
        }

        return $invalid;
    }

    
    private static function getMapByMode(int $mode): array
    {
        if (isset(self::$maps[$mode])) {
            return self::$maps[$mode];
        }

        switch ($mode) {
            case self::LOWER_CASE:
                $file = 'lower';
                break;
            case self::UPPER_CASE:
                $file = 'upper';
                break;
            case self::ASCII_CONV:
                $file = 'ascii';
                break;
            case self::FOLD_CASE:
                $file = 'fold';
                break;
            default:
                return [];
        }

        
        return self::$maps[$mode] = include(__DIR__ . "/../res/{$file}.php");
    }
}
