<?php

declare(strict_types=1);

namespace Sentry;

final class MonitorConfig
{
    
    private $schedule;

    
    private $checkinMargin;

    
    private $maxRuntime;

    
    private $timezone;

    
    private $failureIssueThreshold;

    
    private $recoveryThreshold;

    public function __construct(
        MonitorSchedule $schedule,
        ?int $checkinMargin = null,
        ?int $maxRuntime = null,
        ?string $timezone = null,
        ?int $failureIssueThreshold = null,
        ?int $recoveryThreshold = null
    ) {
        $this->schedule = $schedule;
        $this->checkinMargin = $checkinMargin;
        $this->maxRuntime = $maxRuntime;
        $this->timezone = $timezone;
        $this->failureIssueThreshold = $failureIssueThreshold;
        $this->recoveryThreshold = $recoveryThreshold;
    }

    public function getSchedule(): MonitorSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(MonitorSchedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getCheckinMargin(): ?int
    {
        return $this->checkinMargin;
    }

    public function setCheckinMargin(?int $checkinMargin): self
    {
        $this->checkinMargin = $checkinMargin;

        return $this;
    }

    public function getMaxRuntime(): ?int
    {
        return $this->maxRuntime;
    }

    public function setMaxRuntime(?int $maxRuntime): self
    {
        $this->maxRuntime = $maxRuntime;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getFailureRecoveryThreshold(): ?int
    {
        return $this->failureIssueThreshold;
    }

    public function setFailureRecoveryThreshold(?int $failureIssueThreshold): self
    {
        $this->failureIssueThreshold = $failureIssueThreshold;

        return $this;
    }

    public function getRecoveryThreshold(): ?int
    {
        return $this->recoveryThreshold;
    }

    public function setRecoveryThreshold(?int $recoveryThreshold): self
    {
        $this->recoveryThreshold = $recoveryThreshold;

        return $this;
    }

    
    public function toArray(): array
    {
        return [
            'schedule' => $this->schedule->toArray(),
            'checkin_margin' => $this->checkinMargin,
            'max_runtime' => $this->maxRuntime,
            'timezone' => $this->timezone,
            'failure_issue_threshold' => $this->failureIssueThreshold,
            'recovery_threshold' => $this->recoveryThreshold,
        ];
    }
}
