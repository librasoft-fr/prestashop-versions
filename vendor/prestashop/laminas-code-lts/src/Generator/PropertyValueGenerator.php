<?php

namespace Laminas\Code\Generator;

class PropertyValueGenerator extends ValueGenerator
{
    /** @var int */
    protected $arrayDepth = 1;

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate() . ';';
    }
}
