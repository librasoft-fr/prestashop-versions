<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\Type;


final class TemplateImplements extends Implements_
{
    public function __construct(Type $type, ?Description $description = null)
    {
        parent::__construct($type, $description);
        $this->name = 'template-implements';
    }
}
