<?php



namespace Symfony\Component\Console\Exception;

use Symfony\Component\Console\Messenger\RunCommandContext;


final class RunCommandFailedException extends RuntimeException
{
    public function __construct(\Throwable|string $exception, public readonly RunCommandContext $context)
    {
        parent::__construct(
            $exception instanceof \Throwable ? $exception->getMessage() : $exception,
            $exception instanceof \Throwable && \is_int($exception->getCode()) ? $exception->getCode() : 0,
            $exception instanceof \Throwable ? $exception : null,
        );
    }
}
