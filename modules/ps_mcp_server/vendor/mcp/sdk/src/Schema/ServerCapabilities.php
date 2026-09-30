<?php



namespace Mcp\Schema;


class ServerCapabilities implements \JsonSerializable
{
    
    public function __construct(
        public readonly ?bool $tools = true,
        public readonly ?bool $toolsListChanged = false,
        public readonly ?bool $resources = true,
        public readonly ?bool $resourcesSubscribe = false,
        public readonly ?bool $resourcesListChanged = false,
        public readonly ?bool $prompts = true,
        public readonly ?bool $promptsListChanged = false,
        public readonly ?bool $logging = false,
        public readonly ?bool $completions = false,
        public readonly ?array $experimental = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        $loggingEnabled = isset($data['logging']);
        $completionsEnabled = isset($data['completions']);
        $toolsEnabled = isset($data['tools']);
        $promptsEnabled = isset($data['prompts']);
        $resourcesEnabled = isset($data['resources']);

        $promptsListChanged = null;
        if (isset($data['prompts'])) {
            if (\is_array($data['prompts']) && \array_key_exists('listChanged', $data['prompts'])) {
                $promptsListChanged = (bool) $data['prompts']['listChanged'];
            } elseif (\is_object($data['prompts']) && property_exists($data['prompts'], 'listChanged')) {
                $promptsListChanged = (bool) $data['prompts']->listChanged;
            }
        }

        $resourcesSubscribe = null;
        $resourcesListChanged = null;
        if (isset($data['resources'])) {
            if (\is_array($data['resources']) && \array_key_exists('subscribe', $data['resources'])) {
                $resourcesSubscribe = (bool) $data['resources']['subscribe'];
            } elseif (\is_object($data['resources']) && property_exists($data['resources'], 'subscribe')) {
                $resourcesSubscribe = (bool) $data['resources']->subscribe;
            }
            if (\is_array($data['resources']) && \array_key_exists('listChanged', $data['resources'])) {
                $resourcesListChanged = (bool) $data['resources']['listChanged'];
            } elseif (\is_object($data['resources']) && property_exists($data['resources'], 'listChanged')) {
                $resourcesListChanged = (bool) $data['resources']->listChanged;
            }
        }

        $toolsListChanged = null;
        if (isset($data['tools'])) {
            if (\is_array($data['tools']) && \array_key_exists('listChanged', $data['tools'])) {
                $toolsListChanged = (bool) $data['tools']['listChanged'];
            } elseif (\is_object($data['tools']) && property_exists($data['tools'], 'listChanged')) {
                $toolsListChanged = (bool) $data['tools']->listChanged;
            }
        }

        return new self(
            tools: $toolsEnabled,
            toolsListChanged: $toolsListChanged,
            resources: $resourcesEnabled,
            resourcesSubscribe: $resourcesSubscribe,
            resourcesListChanged: $resourcesListChanged,
            prompts: $promptsEnabled,
            promptsListChanged: $promptsListChanged,
            logging: $loggingEnabled,
            completions: $completionsEnabled,
            experimental: $data['experimental'] ?? null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->logging) {
            $data['logging'] = new \stdClass();
        }
        if ($this->completions) {
            $data['completions'] = new \stdClass();
        }

        if ($this->prompts || $this->promptsListChanged) {
            $data['prompts'] = new \stdClass();
            if ($this->promptsListChanged) {
                $data['prompts']->listChanged = $this->promptsListChanged;
            }
        }

        if ($this->resources || $this->resourcesSubscribe || $this->resourcesListChanged) {
            $data['resources'] = new \stdClass();
            if ($this->resourcesSubscribe) {
                $data['resources']->subscribe = $this->resourcesSubscribe;
            }
            if ($this->resourcesListChanged) {
                $data['resources']->listChanged = $this->resourcesListChanged;
            }
        }

        if ($this->tools || $this->toolsListChanged) {
            $data['tools'] = new \stdClass();
            if ($this->toolsListChanged) {
                $data['tools']->listChanged = $this->toolsListChanged;
            }
        }

        if ($this->experimental) {
            $data['experimental'] = (object) $this->experimental;
        }

        return $data;
    }
}
