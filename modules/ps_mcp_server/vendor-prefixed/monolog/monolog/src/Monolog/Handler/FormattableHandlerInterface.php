<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;

interface FormattableHandlerInterface
{
    public function setFormatter(FormatterInterface $formatter): HandlerInterface;

    public function getFormatter(): FormatterInterface;
}
