<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection;


final class Location
{
    
    private $lineNumber = 0;

    
    private $columnNumber = 0;

    
    public function __construct(int $lineNumber, int $columnNumber = 0)
    {
        $this->lineNumber = $lineNumber;
        $this->columnNumber = $columnNumber;
    }

    
    public function getLineNumber() : int
    {
        return $this->lineNumber;
    }

    
    public function getColumnNumber() : int
    {
        return $this->columnNumber;
    }
}
