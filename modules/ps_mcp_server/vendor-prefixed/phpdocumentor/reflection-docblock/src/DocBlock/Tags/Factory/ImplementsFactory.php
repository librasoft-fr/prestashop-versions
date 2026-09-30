<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Implements_;
use PsMcpServerDeps\phpDocumentor\Reflection\TypeResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class ImplementsFactory implements PHPStanFactory
{
    private DescriptionFactory $descriptionFactory;
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->typeResolver = $typeResolver;
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof ImplementsTagValueNode && $node->name === '@implements';
    }

    public function create(PhpDocTagNode $node, Context $context): Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, ImplementsTagValueNode::class);

        $description = $tagValue->getAttribute('description');
        if (\is_string($description) === false) {
            $description = $tagValue->description;
        }

        return new Implements_(
            $this->typeResolver->createType($tagValue->type, $context),
            $this->descriptionFactory->create($description, $context)
        );
    }
}
