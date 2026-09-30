<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;


interface FormattableHandlerInterface
{
    
    public function setFormatter(FormatterInterface $formatter): HandlerInterface;

    
    public function getFormatter(): FormatterInterface;
}
