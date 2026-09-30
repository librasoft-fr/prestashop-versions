<?php



namespace Symfony\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


final class ConsoleSignalEvent extends ConsoleEvent
{
    private int $handlingSignal;
    private int|false $exitCode;

    public function __construct(Command $command, InputInterface $input, OutputInterface $output, int $handlingSignal, int|false $exitCode = 0)
    {
        parent::__construct($command, $input, $output);
        $this->handlingSignal = $handlingSignal;
        $this->exitCode = $exitCode;
    }

    public function getHandlingSignal(): int
    {
        return $this->handlingSignal;
    }

    public function setExitCode(int $exitCode): void
    {
        if ($exitCode < 0 || $exitCode > 255) {
            throw new \InvalidArgumentException('Exit code must be between 0 and 255.');
        }

        $this->exitCode = $exitCode;
    }

    public function abortExit(): void
    {
        $this->exitCode = false;
    }

    public function getExitCode(): int|false
    {
        return $this->exitCode;
    }
}
