<?php



namespace Symfony\Component\Uid;


abstract class AbstractUid implements \JsonSerializable, \Stringable
{
    
    protected $uid;

    
    abstract public static function isValid(string $uid): bool;

    
    abstract public static function fromString(string $uid): static;

    
    public static function fromBinary(string $uid): static
    {
        if (16 !== \strlen($uid)) {
            throw new \InvalidArgumentException('Invalid binary uid provided.');
        }

        return static::fromString($uid);
    }

    
    public static function fromBase58(string $uid): static
    {
        if (22 !== \strlen($uid)) {
            throw new \InvalidArgumentException('Invalid base-58 uid provided.');
        }

        return static::fromString($uid);
    }

    
    public static function fromBase32(string $uid): static
    {
        if (26 !== \strlen($uid)) {
            throw new \InvalidArgumentException('Invalid base-32 uid provided.');
        }

        return static::fromString($uid);
    }

    
    public static function fromRfc4122(string $uid): static
    {
        if (36 !== \strlen($uid)) {
            throw new \InvalidArgumentException('Invalid RFC4122 uid provided.');
        }

        return static::fromString($uid);
    }

    
    abstract public function toBinary(): string;

    
    public function toBase58(): string
    {
        return strtr(\sprintf('%022s', BinaryUtil::toBase($this->toBinary(), BinaryUtil::BASE58)), '0', '1');
    }

    
    public function toBase32(): string
    {
        $uid = bin2hex($this->toBinary());
        $uid = \sprintf('%02s%04s%04s%04s%04s%04s%04s',
            base_convert(substr($uid, 0, 2), 16, 32),
            base_convert(substr($uid, 2, 5), 16, 32),
            base_convert(substr($uid, 7, 5), 16, 32),
            base_convert(substr($uid, 12, 5), 16, 32),
            base_convert(substr($uid, 17, 5), 16, 32),
            base_convert(substr($uid, 22, 5), 16, 32),
            base_convert(substr($uid, 27, 5), 16, 32)
        );

        return strtr($uid, 'abcdefghijklmnopqrstuv', 'ABCDEFGHJKMNPQRSTVWXYZ');
    }

    
    public function toRfc4122(): string
    {
        
        $uuid = bin2hex($this->toBinary());
        $uuid = substr_replace($uuid, '-', 8, 0);
        $uuid = substr_replace($uuid, '-', 13, 0);
        $uuid = substr_replace($uuid, '-', 18, 0);

        return substr_replace($uuid, '-', 23, 0);
    }

    
    public function toHex(): string
    {
        return '0x'.bin2hex($this->toBinary());
    }

    
    public function equals(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->uid === $other->uid;
    }

    public function compare(self $other): int
    {
        return (\strlen($this->uid) - \strlen($other->uid)) ?: ($this->uid <=> $other->uid);
    }

    public function __toString(): string
    {
        return $this->uid;
    }

    public function jsonSerialize(): string
    {
        return $this->uid;
    }
}
