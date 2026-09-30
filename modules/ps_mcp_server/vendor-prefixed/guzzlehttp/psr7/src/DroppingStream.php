<?php

declare(strict_types=1);

namespace PsMcpServerDeps\GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

final class DroppingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $maxLength;

    private $stream;

    public function __construct(StreamInterface $stream, int $maxLength)
    {
        $this->stream = $stream;
        $this->maxLength = $maxLength;
    }

    public function write($string): int
    {
        if (!\is_string($string)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::write() is deprecated; guzzlehttp/psr7 3.0 requires string for $string.',
                \get_debug_type($string)
            );
        }

        $diff = $this->maxLength - $this->stream->getSize();

        if ($diff <= 0) {
            return 0;
        }

        if (strlen($string) < $diff) {
            return $this->stream->write($string);
        }

        return $this->stream->write(substr($string, 0, $diff));
    }
}
