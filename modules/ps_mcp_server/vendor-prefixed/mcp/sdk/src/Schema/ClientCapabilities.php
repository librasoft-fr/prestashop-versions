<?php

namespace PsMcpServerDeps\Mcp\Schema;

class ClientCapabilities implements \JsonSerializable
{
    public function __construct(
        public readonly ?bool $roots = false,
        public readonly ?bool $rootsListChanged = null,
        public readonly ?bool $sampling = null,
        public readonly ?bool $elicitation = null,
        public readonly ?array $experimental = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $rootsEnabled = isset($data['roots']);
        $rootsListChanged = null;
        if ($rootsEnabled) {
            if (\is_array($data['roots']) && \array_key_exists('listChanged', $data['roots'])) {
                $rootsListChanged = (bool) $data['roots']['listChanged'];
            } elseif (\is_object($data['roots']) && property_exists($data['roots'], 'listChanged')) {
                $rootsListChanged = (bool) $data['roots']->listChanged;
            }
        }

        $sampling = null;
        if (isset($data['sampling'])) {
            $sampling = true;
        }

        $elicitation = null;
        if (isset($data['elicitation'])) {
            $elicitation = true;
        }

        return new self(
            $rootsEnabled,
            $rootsListChanged,
            $sampling,
            $elicitation,
            $data['experimental'] ?? null
        );
    }

    public function jsonSerialize(): array|object
    {
        $data = [];
        if ($this->roots || $this->rootsListChanged) {
            $data['roots'] = new \stdClass();
            if ($this->rootsListChanged) {
                $data['roots']->listChanged = $this->rootsListChanged;
            }
        }

        if ($this->sampling) {
            $data['sampling'] = new \stdClass();
        }

        if ($this->elicitation) {
            $data['elicitation'] = new \stdClass();
        }

        if ($this->experimental) {
            $data['experimental'] = (object) $this->experimental;
        }

        return $data ?: new \stdClass();
    }
}
