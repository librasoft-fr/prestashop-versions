<?php



namespace Symfony\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\Event;


class ConsoleEvent extends Event
{
    protected $command;

    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(?Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }

    
    public function getCommand(): ?Command
    {
        return $this->command;
    }

    
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
