<?php

namespace PsMcpServerDeps\Mcp\Server\Session;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class Session implements SessionInterface
{
    private array $data;

    public function __construct(
        private SessionStoreInterface $store,
        private Uuid $id = new UuidV4(),
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function save(): bool
    {
        return $this->store->write($this->id, json_encode($this->readData(), \JSON_THROW_ON_ERROR));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = explode('.', $key);
        $data = $this->readData();

        foreach ($key as $segment) {
            if (\is_array($data) && \array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } else {
                return $default;
            }
        }

        return $data;
    }

    public function set(string $key, mixed $value, bool $overwrite = true): void
    {
        $segments = explode('.', $key);
        $this->readData();
        $data = &$this->data;

        while (\count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($data[$segment]) || !\is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        $lastKey = array_shift($segments);
        if ($overwrite || !isset($data[$lastKey])) {
            $data[$lastKey] = $value;
        }
    }

    public function has(string $key): bool
    {
        $key = explode('.', $key);
        $data = $this->readData();

        foreach ($key as $segment) {
            if (\is_array($data) && \array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } elseif (\is_object($data) && isset($data->{$segment})) {
                $data = $data->{$segment};
            } else {
                return false;
            }
        }

        return true;
    }

    public function forget(string $key): void
    {
        $segments = explode('.', $key);
        $this->readData();
        $data = &$this->data;

        while (\count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($data[$segment]) || !\is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        $lastKey = array_shift($segments);
        if (isset($data[$lastKey])) {
            unset($data[$lastKey]);
        }
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    public function all(): array
    {
        return $this->readData();
    }

    public function hydrate(array $attributes): void
    {
        $this->data = $attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }

    private function readData(): array
    {
        if (isset($this->data)) {
            return $this->data;
        }

        $rawData = $this->store->read($this->id);

        if (false === $rawData) {
            return $this->data = [];
        }

        $decoded = json_decode($rawData, true, flags: \JSON_THROW_ON_ERROR);

        if (!\is_array($decoded)) {
            return $this->data = [];
        }

        return $this->data = $decoded;
    }
}
