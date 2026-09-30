<?php



namespace Symfony\Component\OptionsResolver\Debug;

use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OptionsResolverIntrospector
{
    private \Closure $get;

    public function __construct(OptionsResolver $optionsResolver)
    {
        $this->get = \Closure::bind(function ($property, $option, $message) {
            
            if (!$this->isDefined($option)) {
                throw new UndefinedOptionsException(\sprintf('The option "%s" does not exist.', $option));
            }

            if (!\array_key_exists($option, $this->{$property})) {
                throw new NoConfigurationException($message);
            }

            return $this->{$property}[$option];
        }, $optionsResolver, $optionsResolver);
    }

    
    public function getDefault(string $option): mixed
    {
        return ($this->get)('defaults', $option, \sprintf('No default value was set for the "%s" option.', $option));
    }

    
    public function getLazyClosures(string $option): array
    {
        return ($this->get)('lazy', $option, \sprintf('No lazy closures were set for the "%s" option.', $option));
    }

    
    public function getAllowedTypes(string $option): array
    {
        return ($this->get)('allowedTypes', $option, \sprintf('No allowed types were set for the "%s" option.', $option));
    }

    
    public function getAllowedValues(string $option): array
    {
        return ($this->get)('allowedValues', $option, \sprintf('No allowed values were set for the "%s" option.', $option));
    }

    
    public function getNormalizer(string $option): \Closure
    {
        return current($this->getNormalizers($option));
    }

    
    public function getNormalizers(string $option): array
    {
        return ($this->get)('normalizers', $option, \sprintf('No normalizer was set for the "%s" option.', $option));
    }

    
    public function getDeprecation(string $option): array
    {
        return ($this->get)('deprecated', $option, \sprintf('No deprecation was set for the "%s" option.', $option));
    }
}
