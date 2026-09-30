<?php



namespace Symfony\Component\Console\Messenger;

use Symfony\Component\Console\Exception\RunCommandFailedException;


class RunCommandMessage implements \Stringable
{
    
    public function __construct(
        public readonly string $input,
        public readonly bool $throwOnFailure = true,
        public readonly bool $catchExceptions = false,
    ) {
    }

    public function __toString(): string
    {
        return $this->input;
    }
}
