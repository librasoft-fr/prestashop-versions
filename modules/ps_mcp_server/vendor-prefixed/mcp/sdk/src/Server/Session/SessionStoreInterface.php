<?php

namespace PsMcpServerDeps\Mcp\Server\Session;

use Symfony\Component\Uid\Uuid;

interface SessionStoreInterface
{
    public function exists(Uuid $id): bool;

    public function read(Uuid $id): string|false;

    public function write(Uuid $id, string $data): bool;

    public function destroy(Uuid $id): bool;

    public function gc(): array;
}
