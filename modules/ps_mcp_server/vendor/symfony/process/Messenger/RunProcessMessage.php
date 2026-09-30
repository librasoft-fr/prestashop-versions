<?php



namespace Symfony\Component\Process\Messenger;


class RunProcessMessage implements \Stringable
{
    public function __construct(
        public readonly array $command,
        public readonly ?string $cwd = null,
        public readonly ?array $env = null,
        public readonly mixed $input = null,
        public readonly ?float $timeout = 60.0,
    ) {
    }

    public function __toString(): string
    {
        return implode(' ', $this->command);
    }
}
