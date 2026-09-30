<?php



namespace Symfony\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


final class ConsoleTerminateEvent extends ConsoleEvent
{
    public function __construct(
        Command $command,
        InputInterface $input,
        OutputInterface $output,
        private int $exitCode,
        private readonly ?int $interruptingSignal = null,
    ) {
        parent::__construct($command, $input, $output);
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getInterruptingSignal(): ?int
    {
        return $this->interruptingSignal;
    }
}
