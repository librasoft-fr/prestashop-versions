<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\Type;


class Extends_ extends TagWithType
{
    public function __construct(Type $type, ?Description $description = null)
    {
        $this->name        = 'extends';
        $this->type        = $type;
        $this->description = $description;
    }

    
    public static function create(string $body): ?Tag
    {
        Deprecation::trigger(
            'phpdocumentor/reflection-docblock',
            'https://github.com/phpDocumentor/ReflectionDocBlock/issues/361',
            'Create using static factory is deprecated, this method should not be called directly
             by library consumers',
        );

        return null;
    }
}
