<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;


trait FormattableHandlerTrait
{
    
    protected $formatter;

    
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    
    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
