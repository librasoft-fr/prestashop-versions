<?php



namespace Symfony\Component\OptionsResolver;

use Symfony\Component\OptionsResolver\Exception\AccessException;

final class OptionConfigurator
{
    private string $name;
    private OptionsResolver $resolver;

    public function __construct(string $name, OptionsResolver $resolver)
    {
        $this->name = $name;
        $this->resolver = $resolver;
        $this->resolver->setDefined($name);
    }

    
    public function allowedTypes(string ...$types): static
    {
        $this->resolver->setAllowedTypes($this->name, $types);

        return $this;
    }

    
    public function allowedValues(mixed ...$values): static
    {
        $this->resolver->setAllowedValues($this->name, $values);

        return $this;
    }

    
    public function default(mixed $value): static
    {
        $this->resolver->setDefault($this->name, $value);

        return $this;
    }

    
    public function define(string $option): self
    {
        return $this->resolver->define($option);
    }

    
    public function deprecated(string $package, string $version, string|\Closure $message = 'The option "%name%" is deprecated.'): static
    {
        $this->resolver->setDeprecated($this->name, $package, $version, $message);

        return $this;
    }

    
    public function normalize(\Closure $normalizer): static
    {
        $this->resolver->setNormalizer($this->name, $normalizer);

        return $this;
    }

    
    public function required(): static
    {
        $this->resolver->setRequired($this->name);

        return $this;
    }

    
    public function info(string $info): static
    {
        $this->resolver->setInfo($this->name, $info);

        return $this;
    }

    
    public function ignoreUndefined(bool $ignore = true): static
    {
        $this->resolver->setIgnoreUndefined($ignore);

        return $this;
    }
}
