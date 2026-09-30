<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Description;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\Fqsen;
use PsMcpServerDeps\phpDocumentor\Reflection\FqsenResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context as TypeContext;
use PsMcpServerDeps\phpDocumentor\Reflection\Utils;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class Covers extends BaseTag implements Factory\StaticMethod
{
    protected string $name = 'covers';

    private Fqsen $refers;

    public function __construct(Fqsen $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }

    public static function create(
        string $body,
        ?DescriptionFactory $descriptionFactory = null,
        ?FqsenResolver $resolver = null,
        ?TypeContext $context = null,
    ): self {
        Assert::stringNotEmpty($body);
        Assert::notNull($descriptionFactory);
        Assert::notNull($resolver);

        $parts = Utils::pregSplit('/\s+/Su', $body, 2);

        return new static(
            self::resolveFqsen($parts[0], $resolver, $context),
            $descriptionFactory->create($parts[1] ?? '', $context)
        );
    }

    private static function resolveFqsen(string $parts, ?FqsenResolver $fqsenResolver, ?TypeContext $context): Fqsen
    {
        Assert::notNull($fqsenResolver);
        $fqsenParts = \explode('::', $parts);
        $resolved = $fqsenResolver->resolve($fqsenParts[0], $context);

        if (!\array_key_exists(1, $fqsenParts)) {
            return $resolved;
        }

        return new Fqsen($resolved . '::' . $fqsenParts[1]);
    }

    public function getReference(): Fqsen
    {
        return $this->refers;
    }

    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        $refers = (string) $this->refers;

        return $refers . ($description !== '' ? ($refers !== '' ? ' ' : '') . $description : '');
    }
}
