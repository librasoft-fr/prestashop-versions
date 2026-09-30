<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast;


abstract class AbstractNodeVisitor implements NodeVisitor 
{

	public function beforeTraverse(array $nodes): ?array
	{
		return null;
	}

	public function enterNode(Node $node)
	{
		return null;
	}

	public function leaveNode(Node $node)
	{
		return null;
	}

	public function afterTraverse(array $nodes): ?array
	{
		return null;
	}

}
