<?php

declare(strict_types=1);



namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;


class Validator extends BaseConstraint
{
    public const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    public const ERROR_NONE                    = 0;
    public const ERROR_ALL                     = -1;
    public const ERROR_DOCUMENT_VALIDATION     = 1;
    public const ERROR_SCHEMA_VALIDATION       = 2;

    
    public function validate(&$value, $schema = null, ?int $checkMode = null): int
    {
        
        $this->reset();

        
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }

        
        $schemaURI = SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        if (LooseTypeCheck::propertyExists($schema, 'id')) {
            $schemaURI = LooseTypeCheck::propertyGet($schema, 'id');
        }
        if (LooseTypeCheck::propertyExists($schema, '$id')) {
            $schemaURI = LooseTypeCheck::propertyGet($schema, '$id');
        }
        $this->factory->getSchemaStorage()->addSchema($schemaURI, $schema);

        $validator = $this->factory->createInstanceFor('schema');
        $schema = $this->factory->getSchemaStorage()->getSchema($schemaURI);

        
        if (is_bool($schema)) {
            if ($schema === false) {
                $this->addError(ConstraintError::FALSE());
            }

            return $this->getErrorMask();
        }

        if ($this->factory->getConfig(Constraint::CHECK_MODE_STRICT)) {
            $dialect = $this->factory->getDefaultDialect();
            if (property_exists($schema, '$schema')) {
                $dialect = $schema->{'$schema'};
            }

            $validator = $this->factory->createInstanceFor(
                DraftIdentifiers::byValue($dialect)->toConstraintName()
            );
        }

        $validator->check($value, $schema);

        $this->factory->setConfig($initialCheckMode);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));

        return $validator->getErrorMask();
    }

    
    public function check($value, $schema): int
    {
        return $this->validate($value, $schema);
    }

    
    public function coerce(&$value, $schema): int
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
