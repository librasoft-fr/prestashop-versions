<?php

declare(strict_types=1);



namespace JsonSchema\Entity;

use JsonSchema\Exception\InvalidArgumentException;


class JsonPointer
{
    
    private $filename;

    
    private $propertyPaths = [];

    
    private $fromDefault = false;

    
    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Ref value must be a string');
        }

        $splitRef = explode('#', $value, 2);
        $this->filename = $splitRef[0];
        if (array_key_exists(1, $splitRef)) {
            $this->propertyPaths = $this->decodePropertyPaths($splitRef[1]);
        }
    }

    
    private function decodePropertyPaths($propertyPathString)
    {
        $paths = [];
        foreach (explode('/', trim($propertyPathString, '/')) as $path) {
            $path = $this->decodePath($path);
            if (is_string($path) && '' !== $path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    
    private function encodePropertyPaths()
    {
        return array_map(
            [$this, 'encodePath'],
            $this->getPropertyPaths()
        );
    }

    
    private function decodePath($path)
    {
        return strtr($path, ['~1' => '/', '~0' => '~', '%25' => '%']);
    }

    
    private function encodePath($path)
    {
        return strtr($path, ['/' => '~1', '~' => '~0', '%' => '%25']);
    }

    
    public function getFilename()
    {
        return $this->filename;
    }

    
    public function getPropertyPaths()
    {
        return $this->propertyPaths;
    }

    
    public function withPropertyPaths(array $propertyPaths)
    {
        $new = clone $this;
        $new->propertyPaths = array_map(static function ($p): string { return (string) $p; }, $propertyPaths);

        return $new;
    }

    
    public function getPropertyPathAsString()
    {
        return rtrim('#/' . implode('/', $this->encodePropertyPaths()), '/');
    }

    
    public function __toString()
    {
        return $this->getFilename() . $this->getPropertyPathAsString();
    }

    
    public function setFromDefault(): void
    {
        $this->fromDefault = true;
    }

    
    public function fromDefault()
    {
        return $this->fromDefault;
    }
}
