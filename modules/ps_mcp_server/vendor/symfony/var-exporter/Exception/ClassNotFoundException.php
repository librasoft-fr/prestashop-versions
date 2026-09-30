<?php



namespace Symfony\Component\VarExporter\Exception;

class ClassNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct(string $class, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Class "%s" not found.', $class), 0, $previous);
    }
}
