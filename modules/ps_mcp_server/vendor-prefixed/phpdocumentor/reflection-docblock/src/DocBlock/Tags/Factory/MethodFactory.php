<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Method;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\TypeResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Void_;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class MethodFactory implements PHPStanFactory
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
        Assert::isInstanceOf($tagValue, MethodTagValueNode::class);

        return new Method(
            $tagValue->methodName,
            [],
            $this->createReturnType($tagValue, $context),
            $tagValue->isStatic,
            $this->descriptionFactory->create($tagValue->description, $context),
            false,
            \array_map(
                function (MethodTagValueParameterNode $param) use ($context) {
                    return new MethodParameter(
                        \trim($param->parameterName, '$'),
                        $param->type === null ? new Mixed_() : $this->typeResolver->createType(
                            $param->type,
                            $context
                        ),
                        $param->isReference,
                        $param->isVariadic,
                        $param->defaultValue === null ?
                            MethodParameter::NO_DEFAULT_VALUE :
                            (string) $param->defaultValue
                    );
                },
                $tagValue->parameters
            ),
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof MethodTagValueNode;
    }

    private function createReturnType(MethodTagValueNode $tagValue, Context $context): Type
    {
        if ($tagValue->returnType === null) {
            return new Void_();
        }

        return $this->typeResolver->createType($tagValue->returnType, $context);
    }
}
