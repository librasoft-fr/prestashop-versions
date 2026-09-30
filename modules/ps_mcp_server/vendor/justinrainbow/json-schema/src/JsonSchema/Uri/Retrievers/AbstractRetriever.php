<?php

declare(strict_types=1);



namespace JsonSchema\Uri\Retrievers;


abstract class AbstractRetriever implements UriRetrieverInterface
{
    
    protected $contentType;

    
    public function getContentType()
    {
        return $this->contentType;
    }
}
