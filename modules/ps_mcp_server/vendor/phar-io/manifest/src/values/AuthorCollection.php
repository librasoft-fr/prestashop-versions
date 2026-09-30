<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use Countable;
use IteratorAggregate;
use function count;


class AuthorCollection implements Countable, IteratorAggregate {
    
    private $authors = [];

    public function add(Author $author): void {
        $this->authors[] = $author;
    }

    
    public function getAuthors(): array {
        return $this->authors;
    }

    public function count(): int {
        return count($this->authors);
    }

    public function getIterator(): AuthorCollectionIterator {
        return new AuthorCollectionIterator($this);
    }
}
