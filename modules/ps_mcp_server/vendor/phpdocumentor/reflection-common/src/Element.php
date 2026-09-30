<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection;


interface Element
{
    
    public function getFqsen() : Fqsen;

    
    public function getName() : string;
}
