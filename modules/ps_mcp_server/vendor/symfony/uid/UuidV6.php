<?php



namespace Symfony\Component\Uid;


class UuidV6 extends Uuid implements TimeBasedUidInterface
{
    protected const TYPE = 6;

    private static string $node;

    public function __construct(?string $uuid = null)
    {
        if (null === $uuid) {
            $this->uid = static::generate();
        } else {
            parent::__construct($uuid, true);
        }
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return BinaryUtil::hexToDateTime('0'.substr($this->uid, 0, 8).substr($this->uid, 9, 4).substr($this->uid, 15, 3));
    }

    public function getNode(): string
    {
        return substr($this->uid, 24);
    }

    public static function generate(?\DateTimeInterface $time = null, ?Uuid $node = null): string
    {
        $uuidV1 = UuidV1::generate($time, $node);
        $uuid = substr($uuidV1, 15, 3).substr($uuidV1, 9, 4).$uuidV1[0].'-'.substr($uuidV1, 1, 4).'-6'.substr($uuidV1, 5, 3).substr($uuidV1, 18, 6);

        if ($node) {
            return $uuid.substr($uuidV1, 24);
        }

        
        

        if (!isset(self::$node)) {
            $seed = [random_int(0, 0xFFFFFF), random_int(0, 0xFFFFFF)];
            $node = unpack('N2', hex2bin('00'.substr($uuidV1, 24, 6)).hex2bin('00'.substr($uuidV1, 30)));
            self::$node = \sprintf('%06x%06x', ($seed[0] ^ $node[1]) | 0x010000, $seed[1] ^ $node[2]);
        }

        return $uuid.self::$node;
    }
}
