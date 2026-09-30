<?php


namespace Opis\JsonSchema\Info;

use Opis\JsonSchema\Uri;

class SchemaInfo
{
    
    protected $data;

    protected ?Uri $id;

    protected ?Uri $root;

    protected ?Uri $base;

    
    protected array $path;

    protected ?string $draft;

    
    public function __construct($data, ?Uri $id, ?Uri $base = null, ?Uri $root = null, array $path = [], ?string $draft = null)
    {
        if ($root === $id || ((string)$root === (string)$id)) {
            $root = null;
        }

        if ($root === null) {
            $base = null;
        }

        $this->data = $data;
        $this->id = $id;
        $this->root = $root;
        $this->base = $base;
        $this->path = $path;
        $this->draft = $draft;
    }

    public function id(): ?Uri
    {
        return $this->id;
    }

    public function root(): ?Uri
    {
       return $this->root;
    }

    public function base(): ?Uri
    {
        return $this->base;
    }

    public function draft(): ?string
    {
        return $this->draft;
    }

    public function data()
    {
        return $this->data;
    }

    public function path(): array
    {
        return $this->path;
    }

    
    public function idBaseRoot(): ?Uri
    {
        return $this->id ?? $this->base ?? $this->root;
    }

    public function isBoolean(): bool
    {
        return is_bool($this->data);
    }

    public function isObject(): bool
    {
        return is_object($this->data);
    }

    public function isDocumentRoot(): bool
    {
        return $this->id && !$this->root && !$this->base;
    }
}