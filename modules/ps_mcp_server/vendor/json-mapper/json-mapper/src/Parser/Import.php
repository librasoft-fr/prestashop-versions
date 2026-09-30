<?php

declare(strict_types=1);

namespace JsonMapper\Parser;

class Import
{
    
    private $import;

    
    private $alias;

    public function __construct(string $import, ?string $alias = null)
    {
        $this->import = $import;
        $this->alias = $alias;
    }

    public function getImport(): string
    {
        return $this->import;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function hasAlias(): bool
    {
        return ! \is_null($this->alias);
    }
}
