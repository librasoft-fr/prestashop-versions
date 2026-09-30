<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class ArrayItem extends Expr
{
    
    public $key;
    
    public $value;
    
    public $byRef;
    
    public $unpack;

    
    public function __construct(Expr $value, ?Expr $key = null, bool $byRef = false, array $attributes = [], bool $unpack = false) {
        $this->attributes = $attributes;
        $this->key = $key;
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }

    public function getSubNodeNames() : array {
        return ['key', 'value', 'byRef', 'unpack'];
    }

    public function getType() : string {
        return 'Expr_ArrayItem';
    }
}
