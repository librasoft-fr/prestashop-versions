<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

final class ExceptionMechanism
{
    public const TYPE_GENERIC = 'generic';

    private $type;

    private $handled;

    private $data;

    public function __construct(string $type, bool $handled, array $data = [])
    {
        $this->type = $type;
        $this->handled = $handled;
        $this->data = $data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isHandled(): bool
    {
        return $this->handled;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
