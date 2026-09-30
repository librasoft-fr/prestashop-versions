<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast;

interface Node
{

	public function __toString(): string;

	
	public function setAttribute(string $key, $value): void;

	public function hasAttribute(string $key): bool;

	
	public function getAttribute(string $key);

}
