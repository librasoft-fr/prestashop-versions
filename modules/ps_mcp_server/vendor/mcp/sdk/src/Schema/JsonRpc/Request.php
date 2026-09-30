<?php



namespace Mcp\Schema\JsonRpc;

use Mcp\Exception\InvalidArgumentException;


abstract class Request implements HasMethodInterface, MessageInterface
{
    protected string|int $id;
    
    protected ?array $meta = null;

    abstract public static function getMethod(): string;

    
    public static function fromArray(array $data): static
    {
        if (($data['jsonrpc'] ?? null) !== MessageInterface::JSONRPC_VERSION) {
            throw new InvalidArgumentException('Invalid or missing "jsonrpc" version for Request.');
        }
        if (!isset($data['id']) || !\is_string($data['id']) && !\is_int($data['id'])) {
            throw new InvalidArgumentException('Invalid or missing "id" for Request.');
        }
        if (!isset($data['method']) || !\is_string($data['method'])) {
            throw new InvalidArgumentException('Invalid or missing "method" for Request.');
        }
        $params = $data['params'] ?? null;
        if ($params instanceof \stdClass) {
            $params = (array) $params;
        }
        if (null !== $params && !\is_array($params)) {
            throw new InvalidArgumentException('"params" for Request must be an array/object or null.');
        }

        $request = static::fromParams($params);
        $request->id = $data['id'];

        if (isset($data['params']['_meta'])) {
            $meta = $data['params']['_meta'];
            if ($meta instanceof \stdClass) {
                $meta = (array) $meta;
            }
            if (\is_array($meta)) {
                $request->meta = $meta;
            }
        }

        return $request;
    }

    
    abstract protected static function fromParams(?array $params): static;

    public function getId(): string|int
    {
        return $this->id;
    }

    
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function withId(string|int $id): static
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    
    public function withMeta(?array $meta): static
    {
        $clone = clone $this;
        $clone->meta = $meta;

        return $clone;
    }

    
    public function jsonSerialize(): array
    {
        $array = [
            'jsonrpc' => MessageInterface::JSONRPC_VERSION,
            'id' => $this->id,
            'method' => static::getMethod(),
        ];
        if (null !== $params = $this->getParams()) {
            $array['params'] = $params;
        }

        if (null !== $this->meta && !isset($params['meta'])) {
            $array['params']['_meta'] = $this->meta;
        }

        return $array;
    }

    
    abstract protected function getParams(): ?array;
}
