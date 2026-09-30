<?php



namespace Symfony\Component\Cache\Marshaller;


class TagAwareMarshaller implements MarshallerInterface
{
    private MarshallerInterface $marshaller;

    public function __construct(?MarshallerInterface $marshaller = null)
    {
        $this->marshaller = $marshaller ?? new DefaultMarshaller();
    }

    public function marshall(array $values, ?array &$failed): array
    {
        $failed = $notSerialized = $serialized = [];

        foreach ($values as $id => $value) {
            if (\is_array($value) && \is_array($value['tags'] ?? null) && \array_key_exists('value', $value) && \count($value) === 2 + (\is_string($value['meta'] ?? null) && 8 === \strlen($value['meta']))) {
                
                

                $v = $this->marshaller->marshall($value, $f);

                if ($f) {
                    $f = [];
                    $failed[] = $id;
                } else {
                    if ([] === $value['tags']) {
                        $v['tags'] = '';
                    }

                    $serialized[$id] = "\x9D".($value['meta'] ?? "\0\0\0\0\0\0\0\0").pack('N', \strlen($v['tags'])).$v['tags'].$v['value'];
                    $serialized[$id][9] = "\x5F";
                }
            } else {
                
                $notSerialized[$id] = $value;
            }
        }

        if ($notSerialized) {
            $serialized += $this->marshaller->marshall($notSerialized, $f);
            $failed = array_merge($failed, $f);
        }

        return $serialized;
    }

    public function unmarshall(string $value): mixed
    {
        
        if (13 >= \strlen($value) || "\x9D" !== $value[0] || "\0" !== $value[5] || "\x5F" !== $value[9]) {
            return $this->marshaller->unmarshall($value);
        }

        
        $meta = substr($value, 1, 12);
        $meta[8] = "\0";
        $tagLen = unpack('Nlen', $meta, 8)['len'];
        $meta = substr($meta, 0, 8);

        return [
            'value' => $this->marshaller->unmarshall(substr($value, 13 + $tagLen)),
            'tags' => $tagLen ? $this->marshaller->unmarshall(substr($value, 13, $tagLen)) : [],
            'meta' => "\0\0\0\0\0\0\0\0" === $meta ? null : $meta,
        ];
    }
}
