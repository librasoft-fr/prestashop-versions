<?php

declare(strict_types=1);



namespace JsonSchema;


interface UriResolverInterface
{
    
    public function resolve($uri, $baseUri = null);
}
