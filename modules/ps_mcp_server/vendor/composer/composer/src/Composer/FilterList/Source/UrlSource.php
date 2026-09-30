<?php declare(strict_types=1);



namespace Composer\FilterList\Source;


class UrlSource
{
    
    public $listName;
    
    public $url;

    public function __construct(
        string $name,
        string $url
    ) {
        $this->listName = $name;
        $this->url = $url;
    }
}
