<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Keyword, Schema, ContentEncoding};
use Opis\JsonSchema\Resolvers\ContentEncodingResolver;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedContentEncodingException;

class ContentEncodingKeyword implements Keyword
{
    use ErrorTrait;

    protected string $name;

    protected ?ContentEncodingResolver $resolver;

    
    protected $encoding = false;

    
    public function __construct(string $name, ?ContentEncodingResolver $resolver = null)
    {
        $this->name = $name;
        $this->resolver = $resolver;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!$this->resolver) {
            return null;
        }

        if ($this->encoding === false) {
            $this->encoding = $this->resolver->resolve($this->name);
        }

        if ($this->encoding === null) {
            throw new UnresolvedContentEncodingException($this->name, $schema, $context);
        }

        $result = $this->encoding instanceof ContentEncoding
            ? $this->encoding->decode($context->currentData(), $this->name)
            : ($this->encoding)($context->currentData(), $this->name);

        if ($result === null) {
            return $this->error($schema, $context, 'contentEncoding', "The value must be encoded as '{encoding}'", [
                'encoding' => $this->name,
            ]);
        }

        $context->setDecodedContent($result);

        return null;
    }
}