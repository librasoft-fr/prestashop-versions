<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{Errors\ValidationError,
    JsonPointer,
    Keyword,
    Schema,
    SchemaLoader,
    Uri,
    ValidationContext,
    Variables};

abstract class AbstractRefKeyword implements Keyword
{
    use ErrorTrait;

    protected string $keyword;
    protected ?Variables $mapper;
    protected ?Variables $globals;
    protected ?array $slots = null;
    protected ?Uri $lastRefUri = null;

    
    protected function __construct(?Variables $mapper, ?Variables $globals, ?array $slots = null, string $keyword = '$ref')
    {
        $this->mapper = $mapper;
        $this->globals = $globals;
        $this->slots = $slots;
        $this->keyword = $keyword;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($error = $this->doValidate($context, $schema)) {
            $uri = $this->lastRefUri;
            $this->lastRefUri = null;

            return $this->error($schema, $context, $this->keyword, 'The data must match {keyword}', [
                'keyword' => $this->keyword,
                'uri' => (string) $uri,
            ], $error);
        }

        $this->lastRefUri = null;

        return null;
    }


    abstract protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError;

    protected function setLastRefUri(?Uri $uri): void
    {
        $this->lastRefUri = $uri;
    }

    protected function setLastRefSchema(Schema $schema): void
    {
        $info = $schema->info();

        if ($info->id()) {
            $this->lastRefUri = $info->id();
        } else {
            $this->lastRefUri = Uri::merge(JsonPointer::pathToFragment($info->path()), $info->idBaseRoot());
        }
    }

    
    protected function createContext(ValidationContext $context, Schema $schema): ValidationContext
    {
        return $context->create($schema, $this->mapper, $this->globals, $this->slots);
    }

    
    protected function resolvePointer(SchemaLoader $repo, JsonPointer $pointer,
        Uri $base, ?array $path = null): ?Schema
    {
        if ($pointer->isAbsolute()) {
            $path = (string)$pointer;
        } else {
            if ($pointer->hasFragment()) {
                return null;
            }

            $path = $path ? $pointer->absolutePath($path) : $pointer->path();
            if ($path === null) {
                return null;
            }

            $path = JsonPointer::pathToString($path);
        }

        return $repo->loadSchemaById(Uri::merge('#' . $path, $base));
    }
}