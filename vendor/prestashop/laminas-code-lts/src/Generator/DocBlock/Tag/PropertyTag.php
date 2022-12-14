<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use function ltrim;

class PropertyTag extends AbstractTypeableTag implements TagInterface
{
    /** @var string */
    protected $propertyName;

    /**
     * @param null|string $propertyName
     * @param array       $types
     * @param null|string $description
     */
    public function __construct($propertyName = null, $types = [], $description = null)
    {
        if (! empty($propertyName)) {
            $this->setPropertyName($propertyName);
        }

        parent::__construct($types, $description);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }

    /**
     * @param string $propertyName
     * @return $this
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = ltrim($propertyName, '$');
        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@property'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->propertyName) ? ' $' . $this->propertyName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
