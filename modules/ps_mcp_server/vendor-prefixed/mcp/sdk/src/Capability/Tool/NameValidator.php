<?php

namespace PsMcpServerDeps\Mcp\Capability\Tool;

final class NameValidator
{
    public function isValid(string $name): bool
    {
        return 1 === preg_match('/^[a-zA-Z0-9._\/-]{1,64}$/', $name);
    }
}
