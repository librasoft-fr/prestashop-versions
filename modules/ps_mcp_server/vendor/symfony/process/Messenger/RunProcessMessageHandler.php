<?php



namespace Symfony\Component\Process\Messenger;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RunProcessFailedException;
use Symfony\Component\Process\Process;


final class RunProcessMessageHandler
{
    public function __invoke(RunProcessMessage $message): RunProcessContext
    {
        $process = new Process($message->command, $message->cwd, $message->env, $message->input, $message->timeout);

        try {
            return new RunProcessContext($message, $process->mustRun());
        } catch (ProcessFailedException $e) {
            throw new RunProcessFailedException($e, new RunProcessContext($message, $e->getProcess()));
        }
    }
}
