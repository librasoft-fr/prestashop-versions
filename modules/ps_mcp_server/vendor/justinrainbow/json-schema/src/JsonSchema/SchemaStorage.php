<?php

declare(strict_types=1);

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;

class SchemaStorage implements SchemaStorageInterface
{
    public const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema/';

    protected $uriRetriever;
    protected $uriResolver;
    protected $schemas = [];

    public function __construct(
        ?UriRetrieverInterface $uriRetriever = null,
        ?UriResolverInterface $uriResolver = null
    ) {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->uriResolver = $uriResolver ?: new UriResolver();
    }

    
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }

    
    public function getUriResolver()
    {
        return $this->uriResolver;
    }

    
    public function addSchema(string $id, $schema = null): void
    {
        if (is_null($schema) && $id !== self::INTERNAL_PROVIDED_SCHEMA_URI) {
            
            
            
            $schema = $this->uriRetriever->retrieve($id);
        }

        
        if (is_array($schema)) {
            $schema = BaseConstraint::arrayToObjectRecursive($schema);
        }

        
        
        if (is_object($schema) && property_exists($schema, 'id')) {
            if ($schema->id === DraftIdentifiers::DRAFT_4) {
                $schema->properties->id->format = 'uri-reference';
            } elseif ($schema->id === DraftIdentifiers::DRAFT_3) {
                $schema->properties->id->format = 'uri-reference';
                $schema->properties->{'$ref'}->format = 'uri-reference';
            }
        }

        $this->scanForSubschemas($schema, $id);

        
        $this->expandRefs($schema, $id);

        $this->schemas[$id] = $schema;
    }

    
    private function expandRefs(&$schema, ?string $parentId = null, array $propertyStack = []): void
    {
        if (!is_object($schema)) {
            if (is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $parentId);
                }
            }

            return;
        }

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $refPointer = new JsonPointer($this->uriResolver->resolve($schema->{'$ref'}, $parentId));
            $schema->{'$ref'} = (string) $refPointer;
        }

        $parentProperty = array_slice($propertyStack, -1)[0] ?? '';
        foreach ($schema as $propertyName => &$member) {
            if ($parentProperty !== 'properties' && in_array($propertyName, ['enum', 'const'])) {
                
                continue;
            }

            $schemaId = $this->findSchemaIdInObject($schema);
            $childId = $parentId;
            if (is_string($schemaId) && $childId !== $schemaId) {
                $childId = $this->uriResolver->resolve($schemaId, $childId);
            }

            $clonedPropertyStack = $propertyStack;
            $clonedPropertyStack[] = $propertyName;
            $this->expandRefs($member, $childId, $clonedPropertyStack);
        }
    }

    
    public function getSchema(string $id)
    {
        if (!array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }

        return $this->schemas[$id];
    }

    
    public function resolveRef(string $ref, $resolveStack = [])
    {
        $jsonPointer = new JsonPointer($ref);

        
        $fileName = $jsonPointer->getFilename();
        if (!strlen($fileName)) {
            throw new UnresolvableJsonPointerException(sprintf(
                "Could not resolve fragment '%s': no file is defined",
                $jsonPointer->getPropertyPathAsString()
            ));
        }

        
        $refSchema = $this->getSchema($fileName);
        foreach ($jsonPointer->getPropertyPaths() as $path) {
            $path = urldecode($path);
            if (is_object($refSchema) && property_exists($refSchema, $path)) {
                $refSchema = $this->resolveRefSchema($refSchema->{$path}, $resolveStack);
            } elseif (is_array($refSchema) && array_key_exists($path, $refSchema)) {
                $refSchema = $this->resolveRefSchema($refSchema[$path], $resolveStack);
            } else {
                throw new UnresolvableJsonPointerException(sprintf(
                    'File: %s is found, but could not resolve fragment: %s',
                    $jsonPointer->getFilename(),
                    $jsonPointer->getPropertyPathAsString()
                ));
            }
        }

        return $refSchema;
    }

    
    public function resolveRefSchema($refSchema, $resolveStack = [])
    {
        if (is_object($refSchema) && property_exists($refSchema, '$ref') && is_string($refSchema->{'$ref'})) {
            if (in_array($refSchema, $resolveStack, true)) {
                throw new UnresolvableJsonPointerException(sprintf(
                    'Dereferencing a pointer to %s results in an infinite loop',
                    $refSchema->{'$ref'}
                ));
            }
            $resolveStack[] = $refSchema;

            return $this->resolveRef($refSchema->{'$ref'}, $resolveStack);
        }

        if (is_object($refSchema) && array_keys(get_object_vars($refSchema)) === ['']) {
            $refSchema = get_object_vars($refSchema)[''];
        }

        return $refSchema;
    }

    
    private function scanForSubschemas($schema, string $parentId): void
    {
        if (!$schema instanceof \stdClass  && !is_array($schema)) {
            return;
        }

        foreach ($schema as $propertyName => $potentialSubSchema) {
            if (!is_object($potentialSubSchema)) {
                if (is_array($potentialSubSchema)) {
                    foreach ($potentialSubSchema as $potentialSubSchemaItem) {
                        $this->scanForSubschemas($potentialSubSchemaItem, $parentId);
                    }
                }
                continue;
            }

            $potentialSubSchemaId = $this->findSchemaIdInObject($potentialSubSchema);
            if (is_string($potentialSubSchemaId) && property_exists($potentialSubSchema, 'type')) {
                
                if (in_array($propertyName, ['enum', 'const'])) {
                    continue;
                }

                
                if (in_array($propertyName, [])) {
                    continue;
                }

                
                $this->addSchema($this->uriResolver->resolve($potentialSubSchemaId, $parentId), $potentialSubSchema);
            }

            $this->scanForSubschemas($potentialSubSchema, $parentId);
        }
    }

    private function findSchemaIdInObject(object $schema): ?string
    {
        if (property_exists($schema, 'id') && is_string($schema->id)) {
            return $schema->id;
        }
        if (property_exists($schema, '$id') && is_string($schema->{'$id'})) {
            return $schema->{'$id'};
        }

        return null;
    }
}
