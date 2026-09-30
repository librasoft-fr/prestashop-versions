<?php



namespace Symfony\Component\Console\Formatter;


final class NullOutputFormatterStyle implements OutputFormatterStyleInterface
{
    public function apply(string $text): string
    {
        return $text;
    }

    public function setBackground(?string $color = null): void
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        
    }

    public function setForeground(?string $color = null): void
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        
    }

    public function setOption(string $option): void
    {
        
    }

    public function setOptions(array $options): void
    {
        
    }

    public function unsetOption(string $option): void
    {
        
    }
}
