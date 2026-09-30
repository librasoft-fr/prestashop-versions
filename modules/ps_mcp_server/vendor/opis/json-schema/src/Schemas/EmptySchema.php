<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;

final class EmptySchema extends AbstractSchema
{
    protected ?KeywordValidator $keywordValidator;

    
    public function __construct(SchemaInfo $info, ?KeywordValidator $keywordValidator = null)
    {
        parent::__construct($info);
        $this->keywordValidator = $keywordValidator;
    }

    
    public function validate(ValidationContext $context): ?ValidationError
    {
        if (!$this->keywordValidator) {
            return null;
        }

        $context->pushSharedObject($this);
        $error = $this->keywordValidator->validate($context);
        $context->popSharedObject();

        return $error;
    }
}