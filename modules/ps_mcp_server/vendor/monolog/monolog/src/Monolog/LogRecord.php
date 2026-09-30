<?php declare(strict_types=1);



namespace Monolog;

use ArrayAccess;


interface LogRecord extends \ArrayAccess
{
    
    public function toArray(): array;
}
