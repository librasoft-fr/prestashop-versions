<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Description;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class TemplateExtends extends Extends_
{
    public function __construct(Type $type, ?Description $description = null)
    {
        parent::__construct($type, $description);
        $this->name = 'template-extends';
    }
}
