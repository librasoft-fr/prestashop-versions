<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\Doctrine\Deprecations\Deprecation;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Description;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;

class Implements_ extends TagWithType
{
    public function __construct(Type $type, ?Description $description = null)
    {
        $this->name = 'implements';
        $this->type = $type;
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
