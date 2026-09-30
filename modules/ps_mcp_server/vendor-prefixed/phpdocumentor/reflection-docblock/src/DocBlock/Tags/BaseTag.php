<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Description;

abstract class BaseTag implements DocBlock\Tag
{
    protected string $name = '';

    protected ?Description $description = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?Description
    {
        return $this->description;
    }

    public function render(?Formatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new Formatter\PassthroughFormatter();
        }

        return $formatter->format($this);
    }
}
