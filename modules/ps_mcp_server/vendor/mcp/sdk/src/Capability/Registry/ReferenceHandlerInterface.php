<?php



namespace Mcp\Capability\Registry;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Exception\RegistryException;


interface ReferenceHandlerInterface
{
    
    public function handle(ElementReference $reference, array $arguments): mixed;
}
