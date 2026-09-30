<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Printer;


class DiffElem
{

	public const TYPE_KEEP = 0;
	public const TYPE_REMOVE = 1;
	public const TYPE_ADD = 2;
	public const TYPE_REPLACE = 3;

	
	public $type;

	
	public $old;

	
	public $new;

	
	public function __construct(int $type, $old, $new)
	{
		$this->type = $type;
		$this->old = $old;
		$this->new = $new;
	}

}
