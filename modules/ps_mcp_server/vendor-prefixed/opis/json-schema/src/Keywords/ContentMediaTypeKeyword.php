<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\ContentMediaType;
use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Exceptions\UnresolvedContentMediaTypeException;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Resolvers\ContentMediaTypeResolver;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

class ContentMediaTypeKeyword implements Keyword
{
    use ErrorTrait;

    protected string $name;

    protected $media = false;

    protected ?ContentMediaTypeResolver $resolver;

    public function __construct(string $name, ?ContentMediaTypeResolver $resolver)
    {
        $this->name = $name;
        $this->resolver = $resolver;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!$this->resolver) {
            return null;
        }

        if ($this->media === false) {
            $this->media = $this->resolver->resolve($this->name);
        }

        if ($this->media === null) {
            throw new UnresolvedContentMediaTypeException($this->name, $schema, $context);
        }

        $data = $context->getDecodedContent();

        $ok = $this->media instanceof ContentMediaType
            ? $this->media->validate($data, $this->name)
            : ($this->media)($data, $this->name);
        if ($ok) {
            return null;
        }

        unset($data);

        return $this->error($schema, $context, 'contentMediaType', "The media type of the data must be '{media}'", [
            'media' => $this->name,
        ]);
    }
}
