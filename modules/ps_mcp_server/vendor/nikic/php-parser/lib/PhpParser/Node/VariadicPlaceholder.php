<?php declare(strict_types=1);

namespace PhpParser\Node;

use PhpParser\NodeAbstract;


class VariadicPlaceholder extends NodeAbstract {
    
    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    public function getType(): string {
        return 'VariadicPlaceholder';
    }

    public function getSubNodeNames(): array {
        return [];
    }
}