<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\Util\SentryUid;

final class CheckIn
{
    
    private $id;

    
    private $monitorSlug;

    
    private $status;

    
    private $release;

    
    private $environment;

    
    private $duration;

    
    private $monitorConfig;

    
    public function __construct(
        string $monitorSlug,
        CheckInStatus $status,
        ?string $id = null,
        ?string $release = null,
        ?string $environment = null,
        $duration = null,
        ?MonitorConfig $monitorConfig = null
    ) {
        $this->setMonitorSlug($monitorSlug);
        $this->setStatus($status);

        $this->setId($id ?? SentryUid::generate());
        $this->setRelease($release ?? '');
        $this->setEnvironment($environment ?? Event::DEFAULT_ENVIRONMENT);
        $this->setDuration($duration);
        $this->setMonitorConfig($monitorConfig);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getMonitorSlug(): string
    {
        return $this->monitorSlug;
    }

    public function setMonitorSlug(string $monitorSlug): self
    {
        $this->monitorSlug = $monitorSlug;

        return $this;
    }

    public function getStatus(): CheckInStatus
    {
        return $this->status;
    }

    public function setStatus(CheckInStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRelease(): ?string
    {
        return $this->release;
    }

    public function setRelease(string $release): self
    {
        $this->release = $release;

        return $this;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    
    public function getDuration()
    {
        return $this->duration;
    }

    
    public function setDuration($duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMonitorConfig(): ?MonitorConfig
    {
        return $this->monitorConfig;
    }

    public function setMonitorConfig(?MonitorConfig $monitorConfig): self
    {
        $this->monitorConfig = $monitorConfig;

        return $this;
    }
}
