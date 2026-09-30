<?php



namespace Symfony\Component\Process\Messenger;

use Symfony\Component\Process\Process;


final class RunProcessContext
{
    public readonly ?int $exitCode;
    public readonly ?string $output;
    public readonly ?string $errorOutput;

    public function __construct(
        public readonly RunProcessMessage $message,
        Process $process,
    ) {
        $this->exitCode = $process->getExitCode();
        $this->output = $process->isOutputDisabled() ? null : $process->getOutput();
        $this->errorOutput = $process->isOutputDisabled() ? null : $process->getErrorOutput();
    }
}
