<?php declare(strict_types=1);

namespace PhpParser\Node\Stmt;

use PhpParser\Node;

class ClassConst extends Node\Stmt
{
    
    public $flags;
    
    public $consts;
    
    public $attrGroups;
    
    public $type;

    
    public function __construct(
        array $consts,
        int $flags = 0,
        array $attributes = [],
        array $attrGroups = [],
        $type = null
    ) {
        $this->attributes = $attributes;
        $this->flags = $flags;
        $this->consts = $consts;
        $this->attrGroups = $attrGroups;
        $this->type = \is_string($type) ? new Node\Identifier($type) : $type;
    }

    public function getSubNodeNames() : array {
        return ['attrGroups', 'flags', 'type', 'consts'];
    }

    
    public function isPublic() : bool {
        return ($this->flags & Class_::MODIFIER_PUBLIC) !== 0
            || ($this->flags & Class_::VISIBILITY_MODIFIER_MASK) === 0;
    }

    
    public function isProtected() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_PROTECTED);
    }

    
    public function isPrivate() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_PRIVATE);
    }

    
    public function isFinal() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_FINAL);
    }

    public function getType() : string {
        return 'Stmt_ClassConst';
    }
}
