<?php

namespace PsMcpServerDeps\Http\Discovery\Exception;

use PsMcpServerDeps\Http\Discovery\Exception;

final class DiscoveryFailedException extends \Exception implements Exception
{
    private $exceptions;

    public function __construct($message, array $exceptions = [])
    {
        $this->exceptions = $exceptions;

        parent::__construct($message);
    }

    public static function create($exceptions)
    {
        $message = 'Could not find resource using any discovery strategy. Find more information at http://docs.php-http.org/en/latest/discovery.html#common-errors';
        foreach ($exceptions as $e) {
            $message .= "\n - " . $e->getMessage();
        }
        $message .= "\n\n";

        return new self($message, $exceptions);
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }
}
