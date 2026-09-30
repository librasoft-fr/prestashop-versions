<?php



namespace Symfony\Component\Process\Exception;

use Symfony\Component\Process\Messenger\RunProcessContext;


final class RunProcessFailedException extends RuntimeException
{
    public function __construct(ProcessFailedException $exception, public readonly RunProcessContext $context)
    {
        parent::__construct($exception->getMessage(), $exception->getCode());
    }
}
