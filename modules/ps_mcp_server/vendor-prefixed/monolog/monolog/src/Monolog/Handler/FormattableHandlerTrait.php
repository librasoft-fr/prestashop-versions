<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\LineFormatter;

trait FormattableHandlerTrait
{
    protected $formatter;

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
