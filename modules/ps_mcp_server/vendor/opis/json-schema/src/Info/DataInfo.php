<?php


namespace Opis\JsonSchema\Info;

use Opis\JsonSchema\ValidationContext;

class DataInfo
{
    
    protected $value;

    protected ?string $type;

    
    protected $root;

    
    protected array $path;

    protected ?DataInfo $parent = null;

    
    protected ?array $fullPath = null;

    
    public function __construct($value, ?string $type, $root, array $path = [], ?DataInfo $parent = null)
    {
        $this->value = $value;
        $this->type = $type;
        $this->root = $root;
        $this->path = $path;
        $this->parent = $parent;
    }

    public function value()
    {
        return $this->value;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function root()
    {
        return $this->root;
    }

    
    public function path(): array
    {
        return $this->path;
    }

    public function parent(): ?DataInfo
    {
        return $this->parent;
    }

    
    public function fullPath(): array
    {
        if ($this->parent === null) {
            return $this->path;
        }

        if ($this->fullPath === null) {
            $this->fullPath = array_merge($this->parent->fullPath(), $this->path);
        }

        return $this->fullPath;
    }

    
    public static function fromContext(ValidationContext $context): self
    {
        if ($parent = $context->parent()) {
            $parent = self::fromContext($parent);
        }

        return new self($context->currentData(), $context->currentDataType(), $context->rootData(),
            $context->currentDataPath(), $parent);
    }
}