<?php

namespace PsMcpServerDeps\Mcp\Capability\Registry;

interface ReferenceHandlerInterface
{
    public function handle(ElementReference $reference, array $arguments): mixed;
}
