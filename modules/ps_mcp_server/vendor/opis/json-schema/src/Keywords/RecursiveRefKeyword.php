<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{Schema, ValidationContext, Variables, Uri};

class RecursiveRefKeyword extends AbstractRefKeyword
{
    protected Uri $uri;
    
    protected $resolved = false;
    protected string $anchor;
    protected $anchorValue;

    public function __construct(
        Uri $uri,
        ?Variables $mapper,
        ?Variables $globals,
        ?array $slots = null,
        string $keyword = '$recursiveRef',
        string $anchor = '$recursiveAnchor',
        $anchorValue = true
    ) {
        parent::__construct($mapper, $globals, $slots, $keyword);
        $this->uri = $uri;
        $this->anchor = $anchor;
        $this->anchorValue = $anchorValue;
    }

    
    public function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->resolved === false) {
            $this->resolved = $context->loader()->loadSchemaById($this->uri);
        }

        if ($this->resolved === null) {
            throw new UnresolvedReferenceException((string)$this->uri, $schema, $context);
        }

        $new_context = $this->createContext($context, $schema);

        if (!$this->hasRecursiveAnchor($this->resolved)) {
            $this->setLastRefSchema($this->resolved);
            return $this->resolved->validate($new_context);
        }

        $ok_sender = $this->resolveSchema($context);

        if (!$ok_sender) {
            $this->setLastRefSchema($this->resolved);
            return $this->resolved->validate($new_context);
        }

        $this->setLastRefSchema($ok_sender);

        return $ok_sender->validate($new_context);
    }

    protected function resolveSchema(ValidationContext $context): ?Schema
    {
        $ok = null;
        $loader = $context->loader();

        while ($context) {
            $sender = $context->sender();

            if (!$sender) {
                break;
            }

            if (!$this->hasRecursiveAnchor($sender)) {
                if ($sender->info()->id()) {
                    
                    break;
                }

                $sender = $loader->loadSchemaById($sender->info()->root());
                if (!$sender || !$this->hasRecursiveAnchor($sender)) {
                    
                    break;
                }
            }

            if ($sender->info()->id()) {
                
                $ok = $sender;
            } else {
                
                $ok = $loader->loadSchemaById($sender->info()->root());
            }

            $context = $context->parent();
        }

        return $ok;
    }

    protected function hasRecursiveAnchor(?Schema $schema): bool
    {
        if (!$schema) {
            return false;
        }

        $info = $schema->info();

        if (!$info->isObject()) {
            return false;
        }

        $data = $info->data();

        if (!property_exists($data, $this->anchor)) {
            return false;
        }

        return $data->{$this->anchor} === $this->anchorValue;
    }
}