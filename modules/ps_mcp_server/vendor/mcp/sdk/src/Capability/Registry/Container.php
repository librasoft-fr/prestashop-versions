<?php



namespace Mcp\Capability\Registry;

use Mcp\Exception\ContainerException;
use Mcp\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;


final class Container implements ContainerInterface
{
    
    private array $instances = [];

    
    private array $resolving = [];

    
    public function get(string $id): mixed
    {
        
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        
        if (!class_exists($id) && !interface_exists($id)) { 
            throw new ServiceNotFoundException(\sprintf('Class, interface, or entry "%s" not found.', $id));
        }

        
        if (isset($this->resolving[$id])) {
            throw new ContainerException("Circular dependency detected while resolving '{$id}'. Resolution path: ".implode(' -> ', array_keys($this->resolving))." -> {$id}");
        }

        $this->resolving[$id] = true; 

        try {
            
            $reflector = new \ReflectionClass($id);

            
            if (!$reflector->isInstantiable()) {
                
                
                throw new ContainerException("Class '{$id}' is not instantiable (e.g., abstract class or interface without explicit binding).");
            }

            
            $constructor = $reflector->getConstructor();

            
            if (null === $constructor || 0 === $constructor->getNumberOfParameters()) {
                $instance = $reflector->newInstance();
            } else {
                
                $parameters = $constructor->getParameters();
                $resolvedArgs = [];

                foreach ($parameters as $parameter) {
                    $resolvedArgs[] = $this->resolveParameter($parameter, $id);
                }

                
                $instance = $reflector->newInstanceArgs($resolvedArgs);
            }

            
            $this->instances[$id] = $instance;

            return $instance;
        } catch (\ReflectionException $e) {
            throw new ContainerException(\sprintf('Reflection failed for %s.', $id), 0, $e);
        } catch (ContainerExceptionInterface $e) { 
            throw $e;
        } catch (\Throwable $e) { 
            throw new ContainerException("Failed to instantiate or resolve dependencies for '{$id}': ".$e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            
            unset($this->resolving[$id]);
        }
    }

    
    private function resolveParameter(\ReflectionParameter $parameter, string $consumerClassId): mixed
    {
        
        $type = $parameter->getType();

        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            
            $typeName = $type->getName();
            try {
                
                return $this->get($typeName);
            } catch (NotFoundExceptionInterface $e) {
                
                if (!$parameter->isOptional() && !$parameter->allowsNull()) {
                    throw new ContainerException("Unresolvable dependency '{$typeName}' required by '{$consumerClassId}' constructor parameter \${$parameter->getName()}.", 0, $e);
                }
                
            } catch (ContainerExceptionInterface $e) {
                
                throw new ContainerException("Failed to resolve dependency '{$typeName}' for '{$consumerClassId}' parameter \${$parameter->getName()}: ".$e->getMessage(), 0, $e);
            }
        }

        
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        
        if ($parameter->allowsNull()) {
            return null;
        }

        
        if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
            throw new ContainerException("Cannot auto-wire built-in type '{$type->getName()}' for required parameter \${$parameter->getName()} in '{$consumerClassId}' constructor. Provide a default value or use a more advanced container.");
        }

        
        if (null !== $type && !$type instanceof \ReflectionNamedType) {
            throw new ContainerException("Cannot auto-wire complex type (union/intersection) for required parameter \${$parameter->getName()} in '{$consumerClassId}' constructor. Provide a default value or use a more advanced container.");
        }

        
        
        throw new ContainerException("Cannot resolve required parameter \${$parameter->getName()} for '{$consumerClassId}' constructor (untyped or unresolvable complex type).");
    }

    
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || class_exists($id) || interface_exists($id);
    }

    
    public function set(string $id, object $instance): void
    {
        
        $this->instances[$id] = $instance;
    }
}
