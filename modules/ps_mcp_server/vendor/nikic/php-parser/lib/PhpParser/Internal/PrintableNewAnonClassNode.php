<?php declare(strict_types=1);

namespace PhpParser\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr;


class PrintableNewAnonClassNode extends Expr
{
    
    public $attrGroups;
    
    public $flags;
    
    public $args;
    
    public $extends;
    
    public $implements;
    
    public $stmts;

    public function __construct(
        array $attrGroups, int $flags, array $args, ?Node\Name $extends, array $implements,
        array $stmts, array $attributes
    ) {
        parent::__construct($attributes);
        $this->attrGroups = $attrGroups;
        $this->flags = $flags;
        $this->args = $args;
        $this->extends = $extends;
        $this->implements = $implements;
        $this->stmts = $stmts;
    }

    public static function fromNewNode(Expr\New_ $newNode) {
        $class = $newNode->class;
        assert($class instanceof Node\Stmt\Class_);
        
        
        return new self(
            $class->attrGroups, $class->flags, $newNode->args, $class->extends, $class->implements,
            $class->stmts, $newNode->getAttributes()
        );
    }

    public function getType() : string {
        return 'Expr_PrintableNewAnonClass';
    }

    public function getSubNodeNames() : array {
        return ['attrGroups', 'flags', 'args', 'extends', 'implements', 'stmts'];
    }
}
