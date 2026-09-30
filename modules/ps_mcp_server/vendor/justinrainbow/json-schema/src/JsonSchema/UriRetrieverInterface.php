<?php

declare(strict_types=1);



namespace JsonSchema;


interface UriRetrieverInterface
{
    
    public function retrieve($uri, $baseUri = null);
}
