<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function array_column;
use function array_filter;
use function array_map;
use function implode;

class PhpDocNode implements Node
{

	use NodeAttributes;

	
	public array $children;

	
	public function __construct(array $children)
	{
		$this->children = $children;
	}

	
	public function getTags(): array
	{
		return array_filter($this->children, static fn (PhpDocChildNode $child): bool => $child instanceof PhpDocTagNode);
	}

	
	public function getTagsByName(string $tagName): array
	{
		return array_filter($this->getTags(), static fn (PhpDocTagNode $tag): bool => $tag->name === $tagName);
	}

	
	public function getVarTagValues(string $tagName = '@var'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof VarTagValueNode,
		);
	}

	
	public function getParamTagValues(string $tagName = '@param'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ParamTagValueNode,
		);
	}

	
	public function getTypelessParamTagValues(string $tagName = '@param'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof TypelessParamTagValueNode,
		);
	}

	
	public function getParamImmediatelyInvokedCallableTagValues(string $tagName = '@param-immediately-invoked-callable'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ParamImmediatelyInvokedCallableTagValueNode,
		);
	}

	
	public function getParamLaterInvokedCallableTagValues(string $tagName = '@param-later-invoked-callable'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ParamLaterInvokedCallableTagValueNode,
		);
	}

	
	public function getParamClosureThisTagValues(string $tagName = '@param-closure-this'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ParamClosureThisTagValueNode,
		);
	}

	
	public function getPureUnlessCallableIsImpureTagValues(string $tagName = '@pure-unless-callable-is-impure'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof PureUnlessCallableIsImpureTagValueNode,
		);
	}

	
	public function getTemplateTagValues(string $tagName = '@template'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof TemplateTagValueNode,
		);
	}

	
	public function getExtendsTagValues(string $tagName = '@extends'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ExtendsTagValueNode,
		);
	}

	
	public function getImplementsTagValues(string $tagName = '@implements'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ImplementsTagValueNode,
		);
	}

	
	public function getUsesTagValues(string $tagName = '@use'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof UsesTagValueNode,
		);
	}

	
	public function getReturnTagValues(string $tagName = '@return'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ReturnTagValueNode,
		);
	}

	
	public function getThrowsTagValues(string $tagName = '@throws'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ThrowsTagValueNode,
		);
	}

	
	public function getMixinTagValues(string $tagName = '@mixin'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof MixinTagValueNode,
		);
	}

	
	public function getRequireExtendsTagValues(string $tagName = '@phpstan-require-extends'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof RequireExtendsTagValueNode,
		);
	}

	
	public function getRequireImplementsTagValues(string $tagName = '@phpstan-require-implements'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof RequireImplementsTagValueNode,
		);
	}

	
	public function getSealedTagValues(string $tagName = '@phpstan-sealed'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof SealedTagValueNode,
		);
	}

	
	public function getDeprecatedTagValues(): array
	{
		return array_filter(
			array_column($this->getTagsByName('@deprecated'), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof DeprecatedTagValueNode,
		);
	}

	
	public function getPropertyTagValues(string $tagName = '@property'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof PropertyTagValueNode,
		);
	}

	
	public function getPropertyReadTagValues(string $tagName = '@property-read'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof PropertyTagValueNode,
		);
	}

	
	public function getPropertyWriteTagValues(string $tagName = '@property-write'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof PropertyTagValueNode,
		);
	}

	
	public function getMethodTagValues(string $tagName = '@method'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof MethodTagValueNode,
		);
	}

	
	public function getTypeAliasTagValues(string $tagName = '@phpstan-type'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof TypeAliasTagValueNode,
		);
	}

	
	public function getTypeAliasImportTagValues(string $tagName = '@phpstan-import-type'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof TypeAliasImportTagValueNode,
		);
	}

	
	public function getAssertTagValues(string $tagName = '@phpstan-assert'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof AssertTagValueNode,
		);
	}

	
	public function getAssertPropertyTagValues(string $tagName = '@phpstan-assert'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof AssertTagPropertyValueNode,
		);
	}

	
	public function getAssertMethodTagValues(string $tagName = '@phpstan-assert'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof AssertTagMethodValueNode,
		);
	}

	
	public function getSelfOutTypeTagValues(string $tagName = '@phpstan-this-out'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof SelfOutTagValueNode,
		);
	}

	
	public function getParamOutTypeTagValues(string $tagName = '@param-out'): array
	{
		return array_filter(
			array_column($this->getTagsByName($tagName), 'value'),
			static fn (PhpDocTagValueNode $value): bool => $value instanceof ParamOutTagValueNode,
		);
	}

	public function __toString(): string
	{
		$children = array_map(
			static function (PhpDocChildNode $child): string {
				$s = (string) $child;
				return $s === '' ? '' : ' ' . $s;
			},
			$this->children,
		);
		return "/**\n *" . implode("\n *", $children) . "\n */";
	}

	
	public static function __set_state(array $properties): self
	{
		$instance = new self($properties['children']);
		if (isset($properties['attributes'])) {
			foreach ($properties['attributes'] as $key => $value) {
				$instance->setAttribute($key, $value);
			}
		}
		return $instance;
	}

}
