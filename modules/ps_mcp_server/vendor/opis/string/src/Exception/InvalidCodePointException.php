<?php


namespace Opis\String\Exception;

use Throwable;

class InvalidCodePointException extends UnicodeException
{
    
    protected $codePoint;

    
    public function __construct($codePoint, ?Throwable $previous = null)
    {
        parent::__construct("Invalid code point", 0, $previous);
        $this->codePoint = $codePoint;
    }

    
    public function codePoint()
    {
        return$this->codePoint;
    }
}
