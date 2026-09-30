<?php

declare(strict_types=1);

namespace Sentry;


final class Frame
{
    public const INTERNAL_FRAME_FILENAME = '[internal]';

    
    public const ANONYMOUS_CLASS_PREFIX = "class@anonymous\x00";

    
    private $functionName;

    
    private $rawFunctionName;

    
    private $file;

    
    private $absoluteFilePath;

    
    private $line;

    
    private $preContext = [];

    
    private $contextLine;

    
    private $postContext = [];

    
    private $inApp;

    
    private $vars = [];

    
    public function __construct(?string $functionName, string $file, int $line, ?string $rawFunctionName = null, ?string $absoluteFilePath = null, array $vars = [], bool $inApp = true)
    {
        $this->functionName = $functionName;
        $this->file = $file;
        $this->line = $line;
        $this->rawFunctionName = $rawFunctionName;
        $this->absoluteFilePath = $absoluteFilePath;
        $this->vars = $vars;
        $this->inApp = $inApp;
    }

    
    public function getFunctionName(): ?string
    {
        return $this->functionName;
    }

    
    public function getRawFunctionName(): ?string
    {
        return $this->rawFunctionName;
    }

    
    public function getFile(): string
    {
        return $this->file;
    }

    
    public function getAbsoluteFilePath(): ?string
    {
        return $this->absoluteFilePath;
    }

    
    public function getLine(): int
    {
        return $this->line;
    }

    
    public function getPreContext(): array
    {
        return $this->preContext;
    }

    
    public function setPreContext(array $preContext): self
    {
        $this->preContext = $preContext;

        return $this;
    }

    
    public function getContextLine(): ?string
    {
        return $this->contextLine;
    }

    
    public function setContextLine(?string $contextLine): self
    {
        $this->contextLine = $contextLine;

        return $this;
    }

    
    public function getPostContext(): array
    {
        return $this->postContext;
    }

    
    public function setPostContext(array $postContext): self
    {
        $this->postContext = $postContext;

        return $this;
    }

    
    public function isInApp(): bool
    {
        return $this->inApp;
    }

    
    public function setIsInApp(bool $inApp): self
    {
        $this->inApp = $inApp;

        return $this;
    }

    
    public function getVars(): array
    {
        return $this->vars;
    }

    
    public function setVars(array $vars): self
    {
        $this->vars = $vars;

        return $this;
    }

    
    public function isInternal(): bool
    {
        return $this->file === self::INTERNAL_FRAME_FILENAME;
    }
}
