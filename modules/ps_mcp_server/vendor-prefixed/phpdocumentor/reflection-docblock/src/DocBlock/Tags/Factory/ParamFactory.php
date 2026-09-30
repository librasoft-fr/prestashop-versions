<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\Doctrine\Deprecations\Deprecation;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Param;
use PsMcpServerDeps\phpDocumentor\Reflection\TypeResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\TypelessParamTagValueNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class ParamFactory implements PHPStanFactory
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

        if ($tagValue instanceof InvalidTagValueNode) {
            Deprecation::trigger(
                'phpdocumentor/reflection-docblock',
                'https://github.com/phpDocumentor/ReflectionDocBlock/issues/362',
                \sprintf(
                    'Param tag value "%s" is invalid, falling back to legacy parsing. Please update your docblocks.',
                    $tagValue->value
                )
            );

            return Param::create($tagValue->value, $this->typeResolver, $this->descriptionFactory, $context);
        }

        Assert::isInstanceOfAny(
            $tagValue,
            [
                ParamTagValueNode::class,
                TypelessParamTagValueNode::class,
            ]
        );

        if (($tagValue->type ?? null) instanceof OffsetAccessTypeNode) {
            return InvalidTag::create(
                (string) $tagValue,
                'param'
            );
        }

        $description = $tagValue->getAttribute('description');
        if (\is_string($description) === false) {
            $description = $tagValue->description;
        }

        return new Param(
            \trim($tagValue->parameterName, '$'),
            $this->typeResolver->createType($tagValue->type ?? new IdentifierTypeNode('mixed'), $context),
            $tagValue->isVariadic,
            $this->descriptionFactory->create($description, $context),
            $tagValue->isReference
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof ParamTagValueNode
            || $node->value instanceof TypelessParamTagValueNode
            || $node->name === '@param';
    }
}
