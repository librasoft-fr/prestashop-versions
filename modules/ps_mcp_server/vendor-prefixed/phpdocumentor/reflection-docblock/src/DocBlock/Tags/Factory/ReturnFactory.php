<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Return_;
use PsMcpServerDeps\phpDocumentor\Reflection\TypeResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class ReturnFactory implements PHPStanFactory
{
    private DescriptionFactory $descriptionFactory;
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->typeResolver = $typeResolver;
    }

    public function create(PhpDocTagNode $node, Context $context): Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, ReturnTagValueNode::class);

        $description = $tagValue->getAttribute('description');
        if (\is_string($description) === false) {
            $description = $tagValue->description;
        }

        return new Return_(
            $this->typeResolver->createType($tagValue->type, $context),
            $this->descriptionFactory->create($description, $context)
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof ReturnTagValueNode;
    }
}
