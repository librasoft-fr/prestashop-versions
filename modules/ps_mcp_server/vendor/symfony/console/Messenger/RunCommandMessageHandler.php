<?php



namespace Symfony\Component\Console\Messenger;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RunCommandFailedException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Exception\RecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;


final class RunCommandMessageHandler
{
    public function __construct(private readonly Application $application)
    {
    }

    public function __invoke(RunCommandMessage $message): RunCommandContext
    {
        $input = new StringInput($message->input);
        $output = new BufferedOutput();

        $this->application->setCatchExceptions($message->catchExceptions);

        try {
            $exitCode = $this->application->run($input, $output);
        } catch (UnrecoverableExceptionInterface|RecoverableExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RunCommandFailedException($e, new RunCommandContext($message, Command::FAILURE, $output->fetch()));
        }

        if ($message->throwOnFailure && Command::SUCCESS !== $exitCode) {
            throw new RunCommandFailedException(\sprintf('Command "%s" exited with code "%s".', $message->input, $exitCode), new RunCommandContext($message, $exitCode, $output->fetch()));
        }

        return new RunCommandContext($message, $exitCode, $output->fetch());
    }
}
