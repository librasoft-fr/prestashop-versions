<?php

namespace PsMcpServerDeps\Mcp\Exception;

final class PromptNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(
        public readonly string $name,
    ) {
        parent::__construct(\sprintf('Prompt not found: "%s".', $name));
    }
}
