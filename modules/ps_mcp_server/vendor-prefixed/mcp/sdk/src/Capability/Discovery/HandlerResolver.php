<?php

namespace PsMcpServerDeps\Mcp\Capability\Discovery;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class HandlerResolver
{
    public static function resolve(\Closure|array|string $handler): \ReflectionMethod|\ReflectionFunction
    {
        if ($handler instanceof \Closure) {
            return new \ReflectionFunction($handler);
        }

        if (\is_array($handler)) {
            if (2 !== \count($handler) || !isset($handler[0]) || !isset($handler[1]) || !\is_string($handler[0]) || !\is_string($handler[1])) {
                throw new InvalidArgumentException('Invalid array handler format. Expected [ClassName::class, \'methodName\'].');
            }
            [$className, $methodName] = $handler;
            if (!class_exists($className)) {
                throw new InvalidArgumentException(\sprintf('Handler class "%s" not found for array handler.', $className));
            }
            if (!method_exists($className, $methodName)) {
                throw new InvalidArgumentException(\sprintf('Handler method "%s" not found in class "%s" for array handler.', $methodName, $className));
            }
        } elseif (class_exists($handler)) {
            $className = $handler;
            $methodName = '__invoke';
            if (!method_exists($className, $methodName)) {
                throw new InvalidArgumentException(\sprintf('Invokable handler class "%s" must have a public "__invoke" method.', $className));
            }
        } else {
            throw new InvalidArgumentException('Invalid handler format. Expected Closure, [ClassName::class, \'methodName\'] or InvokableClassName::class string.');
        }

        try {
            $reflectionMethod = new \ReflectionMethod($className, $methodName);

            if (!$reflectionMethod->isPublic()) {
                throw new InvalidArgumentException(\sprintf('Handler method "%s::%s" must be public.', $className, $methodName));
            }
            if ($reflectionMethod->isAbstract()) {
                throw new InvalidArgumentException(\sprintf('Handler method "%s::%s" must be abstract.', $className, $methodName));
            }
            if ($reflectionMethod->isConstructor() || $reflectionMethod->isDestructor()) {
                throw new InvalidArgumentException(\sprintf('Handler method "%s::%s" cannot be a constructor or destructor.', $className, $methodName));
            }

            return $reflectionMethod;
        } catch (\ReflectionException $e) {
            throw new InvalidArgumentException(\sprintf('Reflection error for handler "%s::%s": %s', $className, $methodName, $e->getMessage()), 0, $e);
        }
    }
}
