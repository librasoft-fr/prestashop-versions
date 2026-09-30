<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class SelfOutTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	public TypeNode $type;

	
	public string $description;

	public function __construct(TypeNode $type, string $description)
	{
		$this->type = $type;
		$this->description = $description;
	}

	public function __toString(): string
	{
		return trim($this->type . ' ' . $this->description);
	}

	
	public static function __set_state(array $properties): self
	{
		$instance = new self($properties['type'], $properties['description']);
		if (isset($properties['attributes'])) {
			foreach ($properties['attributes'] as $key => $value) {
				$instance->setAttribute($key, $value);
			}
		}
		return $instance;
	}

}
