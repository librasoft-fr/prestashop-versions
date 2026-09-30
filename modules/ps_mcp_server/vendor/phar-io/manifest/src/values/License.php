<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class License {
    
    private $name;

    
    private $url;

    public function __construct(string $name, Url $url) {
        $this->name = $name;
        $this->url  = $url;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getUrl(): Url {
        return $this->url;
    }
}
