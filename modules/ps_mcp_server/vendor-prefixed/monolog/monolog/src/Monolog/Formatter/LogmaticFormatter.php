<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Formatter;

class LogmaticFormatter extends JsonFormatter
{
    protected const MARKERS = ['sourcecode', 'php'];

    protected $hostname = '';

    protected $appname = '';

    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function setAppname(string $appname): self
    {
        $this->appname = $appname;

        return $this;
    }

    public function format(array $record): string
    {
        if (!empty($this->hostname)) {
            $record['hostname'] = $this->hostname;
        }
        if (!empty($this->appname)) {
            $record['appname'] = $this->appname;
        }

        $record['@marker'] = static::MARKERS;

        return parent::format($record);
    }
}
