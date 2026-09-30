<?php

namespace PsMcpServerDeps\Mcp\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
