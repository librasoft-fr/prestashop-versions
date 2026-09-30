<?php

namespace PsMcpServerDeps\Mcp\Server\Session;

use Symfony\Component\Uid\Uuid;

interface SessionManagerInterface
{
    public function create(): SessionInterface;

    public function createWithId(Uuid $id): SessionInterface;

    public function exists(Uuid $id): bool;

    public function destroy(Uuid $id): bool;

    public function gc(): void;
}
