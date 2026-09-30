<?php



namespace Symfony\Component\Console\Messenger;


final class RunCommandContext
{
    public function __construct(
        public readonly RunCommandMessage $message,
        public readonly int $exitCode,
        public readonly string $output,
    ) {
    }
}
