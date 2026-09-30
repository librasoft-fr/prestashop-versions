<?php


namespace BrianHenryIE\Strauss\Types;

class DiscoveredSymbols
{
    
    protected array $types = [];

    public function __construct()
    {
        $this->types = [
            T_CLASS => [],
            T_CONST => [],
            T_NAMESPACE => [],
            T_FUNCTION => [],
        ];
    }

    
    public function add(DiscoveredSymbol $symbol): void
    {
        switch (get_class($symbol)) {
            case NamespaceSymbol::class:
                $type = T_NAMESPACE;
                break;
            case ConstantSymbol::class:
                $type = T_CONST;
                break;
            case ClassSymbol::class:
                $type = T_CLASS;
                break;
            case FunctionSymbol::class:
                $type = T_FUNCTION;
                break;
            default:
                throw new \InvalidArgumentException('Unknown symbol type: ' . get_class($symbol));
        }
        $this->types[$type][$symbol->getOriginalSymbol()] = $symbol;
    }

    
    public function getSymbols(): array
    {
        return array_merge(
            array_values($this->getNamespaces()),
            array_values($this->getClasses()),
            array_values($this->getConstants()),
            array_values($this->getDiscoveredFunctions()),
        );
    }

    
    public function getConstants()
    {
        return $this->types[T_CONST];
    }

    
    public function getNamespaces(): array
    {
        return $this->types[T_NAMESPACE];
    }

    
    public function getClasses(): array
    {
        return $this->types[T_CLASS];
    }

    
    public function getDiscoveredNamespaces(?string $namespacePrefix = ''): array
    {
        $discoveredNamespaceReplacements = [];

        
        
        foreach ($this->getNamespaces() as $key => $value) {
            $discoveredNamespaceReplacements[ $value->getOriginalSymbol() ] = $value;
        }

        uksort($discoveredNamespaceReplacements, function ($a, $b) {
            return strlen($a) <=> strlen($b);
        });

        return $discoveredNamespaceReplacements;
    }

    
    public function getDiscoveredClasses(?string $classmapPrefix = ''): array
    {
        $discoveredClasses = $this->getClasses();

        $discoveredClasses = array_filter(
            array_keys($discoveredClasses),
            function (string $replacement) use ($classmapPrefix) {
                return empty($classmapPrefix) || ! str_starts_with($replacement, $classmapPrefix);
            }
        );

        return $discoveredClasses;
    }

    
    public function getDiscoveredConstants(?string $constantsPrefix = ''): array
    {
        $discoveredConstants = $this->getConstants();
        $discoveredConstants = array_filter(
            array_keys($discoveredConstants),
            function (string $replacement) use ($constantsPrefix) {
                return empty($constantsPrefix) || ! str_starts_with($replacement, $constantsPrefix);
            }
        );

        return $discoveredConstants;
    }

    public function getDiscoveredFunctions()
    {
        return $this->types[T_FUNCTION];
    }
}
