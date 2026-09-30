<?php declare(strict_types = 1);
namespace TheSeer\Tokenizer;

class Token {

    
    private $line;

    
    private $name;

    
    private $value;

    
    public function __construct(int $line, string $name, string $value) {
        $this->line  = $line;
        $this->name  = $name;
        $this->value = $value;
    }

    public function getLine(): int {
        return $this->line;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): string {
        return $this->value;
    }
}
