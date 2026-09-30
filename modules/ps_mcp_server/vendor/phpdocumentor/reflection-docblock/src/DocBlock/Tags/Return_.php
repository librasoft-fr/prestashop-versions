<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Return_ extends TagWithType implements Factory\StaticMethod
{
    public function __construct(Type $type, ?Description $description = null)
    {
        $this->name        = 'return';
        $this->type        = $type;
        $this->description = $description;
    }

    
    public static function create(
        string $body,
        ?TypeResolver $typeResolver = null,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ): self {
        Deprecation::triggerIfCalledFromOutside(
            'phpdocumentor/reflection-docblock',
            'https://github.com/phpDocumentor/ReflectionDocBlock/issues/361',
            'Create using static factory is deprecated, this method should not be called directly
             by library consumers',
        );

        Assert::notNull($typeResolver);
        Assert::notNull($descriptionFactory);

        [$type, $description] = self::extractTypeFromBody($body);

        $type        = $typeResolver->resolve($type, $context);
        $description = $descriptionFactory->create($description, $context);

        return new static($type, $description);
    }
}
