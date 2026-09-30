<?php



namespace Symfony\Component\OptionsResolver;

use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;


class OptionsResolver implements Options
{
    private const VALIDATION_FUNCTIONS = [
        'bool' => 'is_bool',
        'boolean' => 'is_bool',
        'int' => 'is_int',
        'integer' => 'is_int',
        'long' => 'is_int',
        'float' => 'is_float',
        'double' => 'is_float',
        'real' => 'is_float',
        'numeric' => 'is_numeric',
        'string' => 'is_string',
        'scalar' => 'is_scalar',
        'array' => 'is_array',
        'iterable' => 'is_iterable',
        'countable' => 'is_countable',
        'callable' => 'is_callable',
        'object' => 'is_object',
        'resource' => 'is_resource',
    ];

    
    private array $defined = [];

    
    private array $defaults = [];

    
    private array $nested = [];

    
    private array $required = [];

    
    private array $resolved = [];

    
    private array $normalizers = [];

    
    private array $allowedValues = [];

    
    private array $allowedTypes = [];

    
    private array $info = [];

    
    private array $lazy = [];

    
    private array $calling = [];

    
    private array $deprecated = [];

    
    private array $given = [];

    
    private bool $locked = false;

    private array $parentsOptions = [];

    
    private ?bool $prototype = null;

    
    private int|string|null $prototypeIndex = null;

    
    private bool $ignoreUndefined = false;

    
    public function setDefault(string $option, mixed $value): static
    {
        
        
        
        if ($this->locked) {
            throw new AccessException('Default values cannot be set from a lazy option or normalizer.');
        }

        
        
        if ($value instanceof \Closure) {
            $reflClosure = new \ReflectionFunction($value);
            $params = $reflClosure->getParameters();

            if (isset($params[0]) && Options::class === $this->getParameterClassName($params[0])) {
                
                if (!isset($this->defaults[$option])) {
                    $this->defaults[$option] = null;
                }

                
                if (!isset($this->lazy[$option]) || !isset($params[1])) {
                    $this->lazy[$option] = [];
                }

                
                $this->lazy[$option][] = $value;
                $this->defined[$option] = true;

                
                unset($this->resolved[$option], $this->nested[$option]);

                return $this;
            }

            if (isset($params[0]) && ($type = $params[0]->getType()) instanceof \ReflectionNamedType && self::class === $type->getName() && (!isset($params[1]) || (($type = $params[1]->getType()) instanceof \ReflectionNamedType && Options::class === $type->getName()))) {
                
                $this->nested[$option][] = $value;
                $this->defaults[$option] = [];
                $this->defined[$option] = true;

                
                unset($this->resolved[$option], $this->lazy[$option]);

                return $this;
            }
        }

        
        unset($this->lazy[$option], $this->nested[$option]);

        
        
        
        
        if (!isset($this->defined[$option]) || \array_key_exists($option, $this->resolved)) {
            $this->resolved[$option] = $value;
        }

        $this->defaults[$option] = $value;
        $this->defined[$option] = true;

        return $this;
    }

    
    public function setDefaults(array $defaults): static
    {
        foreach ($defaults as $option => $value) {
            $this->setDefault($option, $value);
        }

        return $this;
    }

    
    public function hasDefault(string $option): bool
    {
        return \array_key_exists($option, $this->defaults);
    }

    
    public function setRequired(string|array $optionNames): static
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be made required from a lazy option or normalizer.');
        }

        foreach ((array) $optionNames as $option) {
            $this->defined[$option] = true;
            $this->required[$option] = true;
        }

        return $this;
    }

    
    public function isRequired(string $option): bool
    {
        return isset($this->required[$option]);
    }

    
    public function getRequiredOptions(): array
    {
        return array_keys($this->required);
    }

    
    public function isMissing(string $option): bool
    {
        return isset($this->required[$option]) && !\array_key_exists($option, $this->defaults);
    }

    
    public function getMissingOptions(): array
    {
        return array_keys(array_diff_key($this->required, $this->defaults));
    }

    
    public function setDefined(string|array $optionNames): static
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be defined from a lazy option or normalizer.');
        }

        foreach ((array) $optionNames as $option) {
            $this->defined[$option] = true;
        }

        return $this;
    }

    
    public function isDefined(string $option): bool
    {
        return isset($this->defined[$option]);
    }

    
    public function getDefinedOptions(): array
    {
        return array_keys($this->defined);
    }

    public function isNested(string $option): bool
    {
        return isset($this->nested[$option]);
    }

    
    public function setDeprecated(string $option, string $package, string $version, string|\Closure $message = 'The option "%name%" is deprecated.'): static
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be deprecated from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist, defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        if (!\is_string($message) && !$message instanceof \Closure) {
            throw new InvalidArgumentException(\sprintf('Invalid type for deprecation message argument, expected string or \Closure, but got "%s".', get_debug_type($message)));
        }

        
        if ('' === $message) {
            return $this;
        }

        $this->deprecated[$option] = [
            'package' => $package,
            'version' => $version,
            'message' => $message,
        ];

        
        unset($this->resolved[$option]);

        return $this;
    }

    public function isDeprecated(string $option): bool
    {
        return isset($this->deprecated[$option]);
    }

    
    public function setNormalizer(string $option, \Closure $normalizer)
    {
        if ($this->locked) {
            throw new AccessException('Normalizers cannot be set from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        $this->normalizers[$option] = [$normalizer];

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function addNormalizer(string $option, \Closure $normalizer, bool $forcePrepend = false): static
    {
        if ($this->locked) {
            throw new AccessException('Normalizers cannot be set from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        if ($forcePrepend) {
            $this->normalizers[$option] ??= [];
            array_unshift($this->normalizers[$option], $normalizer);
        } else {
            $this->normalizers[$option][] = $normalizer;
        }

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function setAllowedValues(string $option, mixed $allowedValues)
    {
        if ($this->locked) {
            throw new AccessException('Allowed values cannot be set from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        $this->allowedValues[$option] = \is_array($allowedValues) ? $allowedValues : [$allowedValues];

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function addAllowedValues(string $option, mixed $allowedValues)
    {
        if ($this->locked) {
            throw new AccessException('Allowed values cannot be added from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        if (!\is_array($allowedValues)) {
            $allowedValues = [$allowedValues];
        }

        if (!isset($this->allowedValues[$option])) {
            $this->allowedValues[$option] = $allowedValues;
        } else {
            $this->allowedValues[$option] = array_merge($this->allowedValues[$option], $allowedValues);
        }

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function setAllowedTypes(string $option, string|array $allowedTypes)
    {
        if ($this->locked) {
            throw new AccessException('Allowed types cannot be set from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        $this->allowedTypes[$option] = (array) $allowedTypes;

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function addAllowedTypes(string $option, string|array $allowedTypes)
    {
        if ($this->locked) {
            throw new AccessException('Allowed types cannot be added from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        if (!isset($this->allowedTypes[$option])) {
            $this->allowedTypes[$option] = (array) $allowedTypes;
        } else {
            $this->allowedTypes[$option] = array_merge($this->allowedTypes[$option], (array) $allowedTypes);
        }

        
        unset($this->resolved[$option]);

        return $this;
    }

    
    public function define(string $option): OptionConfigurator
    {
        if (isset($this->defined[$option])) {
            throw new OptionDefinitionException(\sprintf('The option "%s" is already defined.', $option));
        }

        return new OptionConfigurator($option, $this);
    }

    
    public function setInfo(string $option, string $info): static
    {
        if ($this->locked) {
            throw new AccessException('The Info message cannot be set from a lazy option or normalizer.');
        }

        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        $this->info[$option] = $info;

        return $this;
    }

    
    public function getInfo(string $option): ?string
    {
        if (!isset($this->defined[$option])) {
            throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
        }

        return $this->info[$option] ?? null;
    }

    
    public function setPrototype(bool $prototype): static
    {
        if ($this->locked) {
            throw new AccessException('The prototype property cannot be set from a lazy option or normalizer.');
        }

        if (null === $this->prototype && $prototype) {
            throw new AccessException('The prototype property cannot be set from a root definition.');
        }

        $this->prototype = $prototype;

        return $this;
    }

    public function isPrototype(): bool
    {
        return $this->prototype ?? false;
    }

    
    public function remove(string|array $optionNames): static
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be removed from a lazy option or normalizer.');
        }

        foreach ((array) $optionNames as $option) {
            unset($this->defined[$option], $this->defaults[$option], $this->required[$option], $this->resolved[$option]);
            unset($this->lazy[$option], $this->normalizers[$option], $this->allowedTypes[$option], $this->allowedValues[$option], $this->info[$option], $this->deprecated[$option]);
        }

        return $this;
    }

    
    public function clear(): static
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be cleared from a lazy option or normalizer.');
        }

        $this->defined = [];
        $this->defaults = [];
        $this->nested = [];
        $this->required = [];
        $this->resolved = [];
        $this->lazy = [];
        $this->normalizers = [];
        $this->allowedTypes = [];
        $this->allowedValues = [];
        $this->deprecated = [];
        $this->info = [];

        return $this;
    }

    
    public function resolve(array $options = []): array
    {
        if ($this->locked) {
            throw new AccessException('Options cannot be resolved from a lazy option or normalizer.');
        }

        
        $clone = clone $this;

        
        $diff = $this->ignoreUndefined ? [] : array_diff_key($options, $clone->defined);

        if (\count($diff) > 0) {
            ksort($clone->defined);
            ksort($diff);

            throw new UndefinedOptionsException(\sprintf((\count($diff) > 1 ? 'The options "%s" do not exist.' : 'The option "%s" does not exist.').' Defined options are: "%s".', $this->formatOptions(array_keys($diff)), implode('", "', array_keys($clone->defined))));
        }

        
        foreach ($options as $option => $value) {
            if ($this->ignoreUndefined && !isset($clone->defined[$option])) {
                continue;
            }

            $clone->given[$option] = true;
            $clone->defaults[$option] = $value;
            unset($clone->resolved[$option], $clone->lazy[$option]);
        }

        
        $diff = array_diff_key($clone->required, $clone->defaults);

        if (\count($diff) > 0) {
            ksort($diff);

            throw new MissingOptionsException(\sprintf(\count($diff) > 1 ? 'The required options "%s" are missing.' : 'The required option "%s" is missing.', $this->formatOptions(array_keys($diff))));
        }

        
        $clone->locked = true;

        
        
        foreach ($clone->defaults as $option => $_) {
            $clone->offsetGet($option);
        }

        return $clone->resolved;
    }

    
    public function offsetGet(mixed $option, bool $triggerDeprecation = true): mixed
    {
        if (!$this->locked) {
            throw new AccessException('Array access is only supported within closures of lazy options and normalizers.');
        }

        
        if (isset($this->resolved[$option]) || \array_key_exists($option, $this->resolved)) {
            if ($triggerDeprecation && isset($this->deprecated[$option]) && (isset($this->given[$option]) || $this->calling) && \is_string($this->deprecated[$option]['message'])) {
                trigger_deprecation($this->deprecated[$option]['package'], $this->deprecated[$option]['version'], strtr($this->deprecated[$option]['message'], ['%name%' => $option]));
            }

            return $this->resolved[$option];
        }

        
        if (!isset($this->defaults[$option]) && !\array_key_exists($option, $this->defaults)) {
            if (!isset($this->defined[$option])) {
                throw new NoSuchOptionException(\sprintf('The option "%s" does not exist. Defined options are: "%s".', $this->formatOptions([$option]), implode('", "', array_keys($this->defined))));
            }

            throw new NoSuchOptionException(\sprintf('The optional option "%s" has no value set. You should make sure it is set with "isset" before reading it.', $this->formatOptions([$option])));
        }

        $value = $this->defaults[$option];

        
        if (isset($this->nested[$option])) {
            
            if (isset($this->calling[$option])) {
                throw new OptionDefinitionException(\sprintf('The options "%s" have a cyclic dependency.', $this->formatOptions(array_keys($this->calling))));
            }

            if (!\is_array($value)) {
                throw new InvalidOptionsException(\sprintf('The nested option "%s" with value %s is expected to be of type array, but is of type "%s".', $this->formatOptions([$option]), $this->formatValue($value), get_debug_type($value)));
            }

            
            $this->calling[$option] = true;
            try {
                $resolver = new self();
                $resolver->prototype = false;
                $resolver->parentsOptions = $this->parentsOptions;

                if ($this->prototype && null !== $this->prototypeIndex && null !== ($parentOptionKey = array_key_last($resolver->parentsOptions))) {
                    $resolver->parentsOptions[$parentOptionKey] .= \sprintf('[%s]', $this->prototypeIndex);
                }

                $resolver->parentsOptions[] = $option;
                foreach ($this->nested[$option] as $closure) {
                    $closure($resolver, $this);
                }

                if ($resolver->prototype) {
                    $values = [];
                    foreach ($value as $index => $prototypeValue) {
                        if (!\is_array($prototypeValue)) {
                            throw new InvalidOptionsException(\sprintf('The value of the option "%s" is expected to be of type array of array, but is of type array of "%s".', $this->formatOptions([$option]), get_debug_type($prototypeValue)));
                        }

                        $resolver->prototypeIndex = $index;
                        $values[$index] = $resolver->resolve($prototypeValue);
                    }
                    $value = $values;
                } else {
                    $value = $resolver->resolve($value);
                }
            } finally {
                $resolver->prototypeIndex = null;
                unset($this->calling[$option]);
            }
        }

        
        if (isset($this->lazy[$option])) {
            
            
            if (isset($this->calling[$option])) {
                throw new OptionDefinitionException(\sprintf('The options "%s" have a cyclic dependency.', $this->formatOptions(array_keys($this->calling))));
            }

            
            
            
            
            $this->calling[$option] = true;
            try {
                foreach ($this->lazy[$option] as $closure) {
                    $value = $closure($this, $value);
                }
            } finally {
                unset($this->calling[$option]);
            }
            
        }

        
        if (isset($this->allowedTypes[$option])) {
            $valid = true;
            $invalidTypes = [];

            foreach ($this->allowedTypes[$option] as $type) {
                if ($valid = $this->verifyTypes($type, $value, $invalidTypes)) {
                    break;
                }
            }

            if (!$valid) {
                $fmtActualValue = $this->formatValue($value);
                $fmtAllowedTypes = implode('" or "', $this->allowedTypes[$option]);
                $fmtProvidedTypes = implode('|', array_keys($invalidTypes));
                $allowedContainsArrayType = \count(array_filter($this->allowedTypes[$option], static fn ($item) => str_ends_with($item, '[]'))) > 0;

                if (\is_array($value) && $allowedContainsArrayType) {
                    throw new InvalidOptionsException(\sprintf('The option "%s" with value %s is expected to be of type "%s", but one of the elements is of type "%s".', $this->formatOptions([$option]), $fmtActualValue, $fmtAllowedTypes, $fmtProvidedTypes));
                }

                throw new InvalidOptionsException(\sprintf('The option "%s" with value %s is expected to be of type "%s", but is of type "%s".', $this->formatOptions([$option]), $fmtActualValue, $fmtAllowedTypes, $fmtProvidedTypes));
            }
        }

        
        if (isset($this->allowedValues[$option])) {
            $success = false;
            $printableAllowedValues = [];

            foreach ($this->allowedValues[$option] as $allowedValue) {
                if ($allowedValue instanceof \Closure) {
                    if ($allowedValue($value)) {
                        $success = true;
                        break;
                    }

                    
                    continue;
                }

                if ($value === $allowedValue) {
                    $success = true;
                    break;
                }

                $printableAllowedValues[] = $allowedValue;
            }

            if (!$success) {
                $message = \sprintf(
                    'The option "%s" with value %s is invalid.',
                    $this->formatOptions([$option]),
                    $this->formatValue($value)
                );

                if (\count($printableAllowedValues) > 0) {
                    $message .= \sprintf(
                        ' Accepted values are: %s.',
                        $this->formatValues($printableAllowedValues)
                    );
                }

                if (isset($this->info[$option])) {
                    $message .= \sprintf(' Info: %s.', $this->info[$option]);
                }

                throw new InvalidOptionsException($message);
            }
        }

        
        
        if ($triggerDeprecation && isset($this->deprecated[$option]) && (isset($this->given[$option]) || ($this->calling && \is_string($this->deprecated[$option]['message'])))) {
            $deprecation = $this->deprecated[$option];
            $message = $this->deprecated[$option]['message'];

            if ($message instanceof \Closure) {
                
                if (isset($this->calling[$option])) {
                    throw new OptionDefinitionException(\sprintf('The options "%s" have a cyclic dependency.', $this->formatOptions(array_keys($this->calling))));
                }

                $this->calling[$option] = true;
                try {
                    if (!\is_string($message = $message($this, $value))) {
                        throw new InvalidOptionsException(\sprintf('Invalid type for deprecation message, expected string but got "%s", return an empty string to ignore.', get_debug_type($message)));
                    }
                } finally {
                    unset($this->calling[$option]);
                }
            }

            if ('' !== $message) {
                trigger_deprecation($deprecation['package'], $deprecation['version'], strtr($message, ['%name%' => $option]));
            }
        }

        
        if (isset($this->normalizers[$option])) {
            
            
            if (isset($this->calling[$option])) {
                throw new OptionDefinitionException(\sprintf('The options "%s" have a cyclic dependency.', $this->formatOptions(array_keys($this->calling))));
            }

            
            
            
            
            $this->calling[$option] = true;
            try {
                foreach ($this->normalizers[$option] as $normalizer) {
                    $value = $normalizer($this, $value);
                }
            } finally {
                unset($this->calling[$option]);
            }
            
        }

        
        $this->resolved[$option] = $value;

        return $value;
    }

    private function verifyTypes(string $type, mixed $value, array &$invalidTypes, int $level = 0): bool
    {
        if (\is_array($value) && str_ends_with($type, '[]')) {
            $type = substr($type, 0, -2);
            $valid = true;

            foreach ($value as $val) {
                if (!$this->verifyTypes($type, $val, $invalidTypes, $level + 1)) {
                    $valid = false;
                }
            }

            return $valid;
        }

        if (('null' === $type && null === $value) || (isset(self::VALIDATION_FUNCTIONS[$type]) ? self::VALIDATION_FUNCTIONS[$type]($value) : $value instanceof $type)) {
            return true;
        }

        if (!$invalidTypes || $level > 0) {
            $invalidTypes[get_debug_type($value)] = true;
        }

        return false;
    }

    
    public function offsetExists(mixed $option): bool
    {
        if (!$this->locked) {
            throw new AccessException('Array access is only supported within closures of lazy options and normalizers.');
        }

        return \array_key_exists($option, $this->defaults);
    }

    
    public function offsetSet(mixed $option, mixed $value): void
    {
        throw new AccessException('Setting options via array access is not supported. Use setDefault() instead.');
    }

    
    public function offsetUnset(mixed $option): void
    {
        throw new AccessException('Removing options via array access is not supported. Use remove() instead.');
    }

    
    public function count(): int
    {
        if (!$this->locked) {
            throw new AccessException('Counting is only supported within closures of lazy options and normalizers.');
        }

        return \count($this->defaults);
    }

    
    public function setIgnoreUndefined(bool $ignore = true): static
    {
        $this->ignoreUndefined = $ignore;

        return $this;
    }

    
    private function formatValue(mixed $value): string
    {
        if (\is_object($value)) {
            return $value::class;
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_string($value)) {
            return '"'.$value.'"';
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (null === $value) {
            return 'null';
        }

        if (false === $value) {
            return 'false';
        }

        if (true === $value) {
            return 'true';
        }

        return (string) $value;
    }

    
    private function formatValues(array $values): string
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->formatValue($value);
        }

        return implode(', ', $values);
    }

    private function formatOptions(array $options): string
    {
        if ($this->parentsOptions) {
            $prefix = array_shift($this->parentsOptions);
            if ($this->parentsOptions) {
                $prefix .= \sprintf('[%s]', implode('][', $this->parentsOptions));
            }

            if ($this->prototype && null !== $this->prototypeIndex) {
                $prefix .= \sprintf('[%s]', $this->prototypeIndex);
            }

            $options = array_map(static fn (string $option): string => \sprintf('%s[%s]', $prefix, $option), $options);
        }

        return implode('", "', $options);
    }

    private function getParameterClassName(\ReflectionParameter $parameter): ?string
    {
        if (!($type = $parameter->getType()) instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return $type->getName();
    }
}
