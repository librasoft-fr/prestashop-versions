<?php declare(strict_types=1);

namespace PhpParser\Node\Stmt;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;

class EnumCase extends Node\Stmt
{
    
    public $name;
    
    public $expr;
    
    public $attrGroups;

    
    public function __construct($name, ?Node\Expr $expr = null, array $attrGroups = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->name = \is_string($name) ? new Node\Identifier($name) : $name;
        $this->expr = $expr;
        $this->attrGroups = $attrGroups;
    }

    public function getSubNodeNames() : array {
        return ['attrGroups', 'name', 'expr'];
    }

    public function getType() : string {
        return 'Stmt_EnumCase';
    }
}
