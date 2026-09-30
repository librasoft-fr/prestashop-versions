<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class GenericTypeNode implements TypeNode
{
    use NodeAttributes;

    public const VARIANCE_INVARIANT = 'invariant';
    public const VARIANCE_COVARIANT = 'covariant';
    public const VARIANCE_CONTRAVARIANT = 'contravariant';
    public const VARIANCE_BIVARIANT = 'bivariant';

    public IdentifierTypeNode $type;

    public array $genericTypes;

    public array $variances;

    public function __construct(IdentifierTypeNode $type, array $genericTypes, array $variances = [])
    {
        $this->type = $type;
        $this->genericTypes = $genericTypes;
        $this->variances = $variances;
    }

    public function __toString(): string
    {
        $genericTypes = [];

        foreach ($this->genericTypes as $index => $type) {
            $variance = $this->variances[$index] ?? self::VARIANCE_INVARIANT;
            if ($variance === self::VARIANCE_INVARIANT) {
                $genericTypes[] = (string) $type;
            } elseif ($variance === self::VARIANCE_BIVARIANT) {
                $genericTypes[] = '*';
            } else {
                $genericTypes[] = \sprintf('%s %s', $variance, $type);
            }
        }

        return $this->type . '<' . \implode(', ', $genericTypes) . '>';
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['genericTypes'], $properties['variances'] ?? []);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
