<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class CopyrightInformation {
    
    private $authors;

    
    private $license;

    public function __construct(AuthorCollection $authors, License $license) {
        $this->authors = $authors;
        $this->license = $license;
    }

    public function getAuthors(): AuthorCollection {
        return $this->authors;
    }

    public function getLicense(): License {
        return $this->license;
    }
}
