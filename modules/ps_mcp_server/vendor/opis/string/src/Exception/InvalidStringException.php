<?php


namespace Opis\String\Exception;

use Throwable;

class InvalidStringException extends UnicodeException
{
    
    protected string $string;

    
    protected int $offset;

    
    public function __construct(string $string, int $offset = -1, ?Throwable $previous = null)
    {
        parent::__construct("Invalid UTF-8 string at offset {$offset}", 0, $previous);
        $this->string = $string;
        $this->offset = $offset;
    }

    
    public function string(): string
    {
        return $this->string;
    }

    
    public function offset(): int
    {
        return $this->offset;
    }
}
