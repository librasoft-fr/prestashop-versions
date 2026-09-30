<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

final class MonitorSchedule
{
    private $type;

    private $value;

    private $unit;

    public const TYPE_CRONTAB = 'crontab';

    public const TYPE_INTERVAL = 'interval';

    public function __construct(
        string $type,
        $value,
        ?MonitorScheduleUnit $unit = null,
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->unit = $unit;
    }

    public static function crontab(string $value): self
    {
        return new self(self::TYPE_CRONTAB, $value);
    }

    public static function interval(int $value, MonitorScheduleUnit $unit): self
    {
        return new self(self::TYPE_INTERVAL, $value, $unit);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getUnit(): ?MonitorScheduleUnit
    {
        return $this->unit;
    }

    public function setUnit(?MonitorScheduleUnit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
            'unit' => (string) $this->unit,
        ];
    }
}
