<?php



namespace Symfony\Component\Cache\Marshaller;

use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Exception\InvalidArgumentException;


class SodiumMarshaller implements MarshallerInterface
{
    private MarshallerInterface $marshaller;
    private array $decryptionKeys;

    
    public function __construct(array $decryptionKeys, ?MarshallerInterface $marshaller = null)
    {
        if (!self::isSupported()) {
            throw new CacheException('The "sodium" PHP extension is not loaded.');
        }

        if (!isset($decryptionKeys[0])) {
            throw new InvalidArgumentException('At least one decryption key must be provided at index "0".');
        }

        $this->marshaller = $marshaller ?? new DefaultMarshaller();
        $this->decryptionKeys = $decryptionKeys;
    }

    public static function isSupported(): bool
    {
        return \function_exists('sodium_crypto_box_seal');
    }

    public function marshall(array $values, ?array &$failed): array
    {
        $encryptionKey = sodium_crypto_box_publickey($this->decryptionKeys[0]);

        $encryptedValues = [];
        foreach ($this->marshaller->marshall($values, $failed) as $k => $v) {
            $encryptedValues[$k] = sodium_crypto_box_seal($v, $encryptionKey);
        }

        return $encryptedValues;
    }

    public function unmarshall(string $value): mixed
    {
        foreach ($this->decryptionKeys as $k) {
            if (false !== $decryptedValue = @sodium_crypto_box_seal_open($value, $k)) {
                $value = $decryptedValue;
                break;
            }
        }

        return $this->marshaller->unmarshall($value);
    }
}
