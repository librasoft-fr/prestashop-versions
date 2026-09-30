<?php


namespace Opis\JsonSchema\Keywords;

use Opis\Uri\UriTemplate;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{JsonPointer, Schema, SchemaLoader, Uri, ValidationContext, Variables};

class TemplateRefKeyword extends AbstractRefKeyword
{
    protected UriTemplate $template;
    protected ?Variables $vars = null;
    
    protected ?array $cached = [];
    protected bool $allowRelativeJsonPointerInRef;

    public function __construct(
        UriTemplate $template,
        ?Variables $vars,
        ?Variables $mapper = null,
        ?Variables $globals = null,
        ?array $slots = null,
        string $keyword = '$ref',
        bool $allowRelativeJsonPointerInRef = true
    ) {
        parent::__construct($mapper, $globals, $slots, $keyword);
        $this->template = $template;
        $this->vars = $vars;
        $this->allowRelativeJsonPointerInRef = $allowRelativeJsonPointerInRef;
    }

    protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->vars) {
            $vars = $this->vars->resolve($context->rootData(), $context->currentDataPath());
            if (!is_array($vars)) {
                $vars = (array)$vars;
            }
            $vars += $context->globals();
        } else {
            $vars = $context->globals();
        }

        $ref = $this->template->resolve($vars);

        $key = isset($ref[32]) ? md5($ref) : $ref;

        if (!array_key_exists($key, $this->cached)) {
            $this->cached[$key] = $this->resolveRef($ref, $context->loader(), $schema);
        }

        $resolved = $this->cached[$key];
        unset($key);

        if (!$resolved) {
            throw new UnresolvedReferenceException($ref, $schema, $context);
        }

        return $resolved->validate($this->createContext($context, $schema));
    }

    
    protected function resolveRef(string $ref, SchemaLoader $repo, Schema $schema): ?Schema
    {
        if ($ref === '') {
            return null;
        }

        $baseUri = $schema->info()->idBaseRoot();

        if ($ref === '#') {
            return $repo->loadSchemaById($baseUri);
        }

        
        if ($ref[0] === '#') {
            if ($pointer = JsonPointer::parse(substr($ref, 1))) {
                if ($pointer->isAbsolute()) {
                    return $this->resolvePointer($repo, $pointer, $baseUri);
                }
                unset($pointer);
            }
        } elseif ($this->allowRelativeJsonPointerInRef && ($pointer = JsonPointer::parse($ref))) {
            if ($pointer->isRelative()) {
                return $this->resolvePointer($repo, $pointer, $baseUri, $schema->info()->path());
            }
            unset($pointer);
        }

        $ref = Uri::merge($ref, $baseUri, true);

        if ($ref === null || !$ref->isAbsolute()) {
            return null;
        }

        return $repo->loadSchemaById($ref);
    }
}