<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\State;

use PsMcpServerDeps\Sentry\Logs\LogsAggregator;
use PsMcpServerDeps\Sentry\Metrics\MetricsAggregator;

final class RuntimeContext
{
    private $id;

    private $hub;

    private $logsAggregator;

    private $metricsAggregator;

    public function __construct(string $id, HubInterface $hub)
    {
        $this->id = $id;
        $this->hub = $hub;
        $this->logsAggregator = new LogsAggregator();
        $this->metricsAggregator = new MetricsAggregator();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHub(): HubInterface
    {
        return $this->hub;
    }

    public function setHub(HubInterface $hub): void
    {
        $this->hub = $hub;
    }

    public function getLogsAggregator(): LogsAggregator
    {
        return $this->logsAggregator;
    }

    public function getMetricsAggregator(): MetricsAggregator
    {
        return $this->metricsAggregator;
    }
}
