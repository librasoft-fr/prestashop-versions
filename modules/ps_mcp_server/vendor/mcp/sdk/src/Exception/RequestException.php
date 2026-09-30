<?php



namespace Mcp\Exception;

use Mcp\Schema\JsonRpc\Error;


class RequestException extends Exception
{
    private ?Error $error;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?Error $error = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
    }

    public static function fromError(Error $error): self
    {
        return new self($error->message, $error->code, null, $error);
    }

    public function getError(): ?Error
    {
        return $this->error;
    }
}
