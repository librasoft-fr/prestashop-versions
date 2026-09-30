<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeVisitor;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Attribute;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Node;

final class CloningVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $originalNode): Node
    {
        $node = clone $originalNode;
        $node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);

        return $node;
    }
}
