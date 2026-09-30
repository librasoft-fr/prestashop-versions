<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Schemas;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\KeywordValidator;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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
