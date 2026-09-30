<?php



namespace Symfony\Component\Uid;


class UuidV1 extends Uuid implements TimeBasedUidInterface
{
    protected const TYPE = 1;

    private static string $clockSeq;

    public function __construct(?string $uuid = null)
    {
        if (null === $uuid) {
            $this->uid = strtolower(uuid_create(static::TYPE));
        } else {
            parent::__construct($uuid, true);
        }
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return BinaryUtil::hexToDateTime('0'.substr($this->uid, 15, 3).substr($this->uid, 9, 4).substr($this->uid, 0, 8));
    }

    public function getNode(): string
    {
        return uuid_mac($this->uid);
    }

    public static function generate(?\DateTimeInterface $time = null, ?Uuid $node = null): string
    {
        $uuid = !$time || !$node ? uuid_create(static::TYPE) : parent::NIL;

        if ($time) {
            if ($node) {
                
                $seq = substr($node->uid, 19, 4);
            } elseif (!$seq = self::$clockSeq ?? '') {
                
                $seq = substr($uuid, 19, 4);

                do {
                    self::$clockSeq = \sprintf('%04x', random_int(0, 0x3FFF) | 0x8000);
                } while ($seq === self::$clockSeq);

                $seq = self::$clockSeq;
            }

            $time = BinaryUtil::dateTimeToHex($time);
            $uuid = substr($time, 8).'-'.substr($time, 4, 4).'-1'.substr($time, 1, 3).'-'.$seq.substr($uuid, 23);
        }

        if ($node) {
            $uuid = substr($uuid, 0, 24).substr($node->uid, 24);
        }

        return $uuid;
    }
}
