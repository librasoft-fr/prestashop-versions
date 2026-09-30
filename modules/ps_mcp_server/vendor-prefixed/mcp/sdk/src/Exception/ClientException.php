<?php

namespace PsMcpServerDeps\Mcp\Exception;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;

class ClientException extends Exception
{
    public function __construct(
        private readonly Error $error,
    ) {
        parent::__construct($error->message);
    }

    public function getError(): Error
    {
        return $this->error;
    }
}
