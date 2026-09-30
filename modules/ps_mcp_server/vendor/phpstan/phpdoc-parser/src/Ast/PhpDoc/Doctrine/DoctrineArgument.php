<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;


class DoctrineArgument implements Node
{

	use NodeAttributes;

	public ?IdentifierTypeNode $key = null;

	
	public $value;

	
	public function __construct(?IdentifierTypeNode $key, $value)
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function __toString(): string
	{
		if ($this->key === null) {
			return (string) $this->value;
		}

		return $this->key . '=' . $this->value;
	}

	
	public static function __set_state(array $properties): self
	{
		$instance = new self($properties['key'], $properties['value']);
		if (isset($properties['attributes'])) {
			foreach ($properties['attributes'] as $key => $value) {
				$instance->setAttribute($key, $value);
			}
		}
		return $instance;
	}

}
