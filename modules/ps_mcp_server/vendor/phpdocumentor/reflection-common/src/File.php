<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection;


interface File
{
    
    public function getContents() : string;

    
    public function md5() : string;

    
    public function path() : string;
}
