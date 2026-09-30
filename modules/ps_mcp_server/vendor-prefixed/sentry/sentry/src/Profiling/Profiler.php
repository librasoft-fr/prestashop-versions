<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Profiling;

use PsMcpServerDeps\Sentry\Options;
use Psr\Log\NullLogger;

final class Profiler
{
    private $profiler;

    private $profile;

    private $logger;

    private const SAMPLE_RATE = 0.0101;

    private const MAX_STACK_DEPTH = 128;

    public function __construct(?Options $options = null)
    {
        $this->logger = $options !== null ? $options->getLoggerOrNullLogger() : new NullLogger();
        $this->profile = new Profile($options);

        $this->initProfiler();
    }

    public function start(): void
    {
        if ($this->profiler !== null) {
            $this->profiler->start();
        }
    }

    public function stop(): void
    {
        if ($this->profiler !== null) {
            $this->profiler->stop();

            $this->profile->setExcimerLog($this->profiler->flush());
        }
    }

    public function getProfile(): ?Profile
    {
        if ($this->profiler === null) {
            return null;
        }

        return $this->profile;
    }

    private function initProfiler(): void
    {
        if (!\extension_loaded('excimer')) {
            $this->logger->warning('The profiler was started but is not available because the "excimer" extension is not loaded.');

            return;
        }

        $this->profiler = new \ExcimerProfiler();
        $this->profile->setStartTimeStamp(microtime(true));

        $this->profiler->setEventType(\EXCIMER_REAL);
        $this->profiler->setPeriod(self::SAMPLE_RATE);
        $this->profiler->setMaxDepth(self::MAX_STACK_DEPTH);
    }
}
