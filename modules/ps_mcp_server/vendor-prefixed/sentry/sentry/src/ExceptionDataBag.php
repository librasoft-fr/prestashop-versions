<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

final class ExceptionDataBag
{
    private $type;

    private $value;

    private $stacktrace;

    private $mechanism;

    public function __construct(\Throwable $exception, ?Stacktrace $stacktrace = null, ?ExceptionMechanism $mechanism = null)
    {
        $this->type = \get_class($exception);
        $this->value = $exception->getMessage();
        $this->stacktrace = $stacktrace;
        $this->mechanism = $mechanism;
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getStacktrace(): ?Stacktrace
    {
        return $this->stacktrace;
    }

    public function setStacktrace(Stacktrace $stacktrace): self
    {
        $this->stacktrace = $stacktrace;

        return $this;
    }

    public function getMechanism(): ?ExceptionMechanism
    {
        return $this->mechanism;
    }

    public function setMechanism(?ExceptionMechanism $mechanism): self
    {
        $this->mechanism = $mechanism;

        return $this;
    }
}
