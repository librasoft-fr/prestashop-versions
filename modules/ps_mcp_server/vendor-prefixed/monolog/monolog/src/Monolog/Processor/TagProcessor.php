<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Processor;

class TagProcessor implements ProcessorInterface
{
    private $tags;

    public function __construct(array $tags = [])
    {
        $this->setTags($tags);
    }

    public function addTags(array $tags = []): self
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    public function setTags(array $tags = []): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function __invoke(array $record): array
    {
        $record['extra']['tags'] = $this->tags;

        return $record;
    }
}
