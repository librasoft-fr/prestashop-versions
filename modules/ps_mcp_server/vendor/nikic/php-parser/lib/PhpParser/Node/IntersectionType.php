<?php declare(strict_types=1);

namespace PhpParser\Node;

use PhpParser\NodeAbstract;

class IntersectionType extends ComplexType
{
    
    public $types;

    
    public function __construct(array $types, array $attributes = []) {
        $this->attributes = $attributes;
        $this->types = $types;
    }

    public function getSubNodeNames() : array {
        return ['types'];
    }

    public function getType() : string {
        return 'IntersectionType';
    }
}
