<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{Schema, Uri, ValidationContext, Variables};

class URIRefKeyword extends AbstractRefKeyword
{
    protected Uri $uri;
    
    protected $resolved = false;

    public function __construct(
        Uri $uri,
        ?Variables $mapper,
        ?Variables $globals,
        ?array $slots = null,
        string $keyword = '$ref'
    ) {
        parent::__construct($mapper, $globals, $slots, $keyword);
        $this->uri = $uri;
    }

    protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->resolved === false) {
            $this->resolved = $context->loader()->loadSchemaById($this->uri);
        }

        if ($this->resolved === null) {
            throw new UnresolvedReferenceException((string)$this->uri, $schema, $context);
        }

        $this->setLastRefSchema($this->resolved);

        return $this->resolved->validate($this->createContext($context, $schema));
    }
}