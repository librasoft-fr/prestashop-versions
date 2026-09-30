<?php



namespace Mcp\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
