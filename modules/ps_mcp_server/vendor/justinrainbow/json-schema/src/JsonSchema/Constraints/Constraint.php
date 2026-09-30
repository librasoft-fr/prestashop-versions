<?php

declare(strict_types=1);

namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;

abstract class Constraint extends BaseConstraint implements ConstraintInterface
{
    
    protected $inlineSchemaProperty = '$schema';

    
    public const CHECK_MODE_NONE =             0x00000000;
    public const CHECK_MODE_NORMAL =           0x00000001;
    public const CHECK_MODE_TYPE_CAST =        0x00000002;
    public const CHECK_MODE_COERCE_TYPES =     0x00000004;
    public const CHECK_MODE_APPLY_DEFAULTS =   0x00000008;
    public const CHECK_MODE_EXCEPTIONS =       0x00000010;
    public const CHECK_MODE_DISABLE_FORMAT =   0x00000020;
    public const CHECK_MODE_EARLY_COERCE =     0x00000040;
    public const CHECK_MODE_ONLY_REQUIRED_DEFAULTS   = 0x00000080;
    public const CHECK_MODE_VALIDATE_SCHEMA =  0x00000100;
    public const CHECK_MODE_STRICT =  0x00000200;

    
    protected function incrementPath(?JsonPointer $path, $i): JsonPointer
    {
        $path = $path ?? new JsonPointer('');

        if ($i === null || $i === '') {
            return $path;
        }

        return $path->withPropertyPaths(
            array_merge(
                $path->getPropertyPaths(),
                [$i]
            )
        );
    }

    
    protected function checkArray(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('collection');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkObject(
        &$value,
        $schema = null,
        ?JsonPointer $path = null,
        $properties = null,
        $additionalProperties = null,
        $patternProperties = null,
        array $appliedDefaults = []
    ): void {
        
        $validator = $this->factory->createInstanceFor('object');
        $validator->check($value, $schema, $path, $properties, $additionalProperties, $patternProperties, $appliedDefaults);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkType(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('type');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkUndefined(&$value, $schema = null, ?JsonPointer $path = null, $i = null, bool $fromDefault = false): void
    {
        
        $validator = $this->factory->createInstanceFor('undefined');

        $validator->check($value, $this->factory->getSchemaStorage()->resolveRefSchema($schema), $path, $i, $fromDefault);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkString($value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('string');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkNumber($value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('number');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkEnum($value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('enum');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkConst($value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('const');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function checkFormat($value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor('format');
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    
    protected function getTypeCheck(): TypeCheck\TypeCheckInterface
    {
        return $this->factory->getTypeCheck();
    }
}
