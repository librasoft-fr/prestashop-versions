<?php



namespace Symfony\Component\Console\Formatter;


interface OutputFormatterStyleInterface
{
    
    public function setForeground(?string $color);

    
    public function setBackground(?string $color);

    
    public function setOption(string $option);

    
    public function unsetOption(string $option);

    
    public function setOptions(array $options);

    
    public function apply(string $text): string;
}
