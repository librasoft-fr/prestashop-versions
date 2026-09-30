<?php



namespace Symfony\Component\VarExporter\Exception;

class NotInstantiableTypeException extends \Exception implements ExceptionInterface
{
    public function __construct(string $type, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Type "%s" is not instantiable.', $type), 0, $previous);
    }
}
