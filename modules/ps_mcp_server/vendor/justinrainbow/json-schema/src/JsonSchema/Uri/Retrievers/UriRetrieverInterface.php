<?php

declare(strict_types=1);



namespace JsonSchema\Uri\Retrievers;


interface UriRetrieverInterface
{
    
    public function retrieve($uri);

    
    public function getContentType();
}
