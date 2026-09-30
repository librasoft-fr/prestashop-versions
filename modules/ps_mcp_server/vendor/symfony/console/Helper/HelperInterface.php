<?php



namespace Symfony\Component\Console\Helper;


interface HelperInterface
{
    
    public function setHelperSet(?HelperSet $helperSet);

    
    public function getHelperSet(): ?HelperSet;

    
    public function getName();
}
