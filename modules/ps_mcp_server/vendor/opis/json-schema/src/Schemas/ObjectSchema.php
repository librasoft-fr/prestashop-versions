<?php


namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\{Helper, Keyword, ValidationContext, KeywordValidator};
use Opis\JsonSchema\Info\{DataInfo, SchemaInfo};
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\CallbackKeywordValidator;

class ObjectSchema extends AbstractSchema
{
    protected ?KeywordValidator $keywordValidator = null;

    
    protected ?array $before = null;

    
    protected ?array $after = null;

    
    protected ?array $types = null;

    
    public function __construct(SchemaInfo $info, ?KeywordValidator $keywordValidator, ?array $types, ?array $before, ?array $after)
    {
        parent::__construct($info);
        $this->types = $types;
        $this->before = $before;
        $this->after = $after;
        $this->keywordValidator = $keywordValidator;

        if ($keywordValidator) {
            while ($next = $keywordValidator->next()) {
                $keywordValidator = $next;
            }
            $keywordValidator->setNext(new CallbackKeywordValidator([$this, 'doValidate']));
        }
    }

    
    public function validate(ValidationContext $context): ?ValidationError
    {
        $context->pushSharedObject($this);
        $error = $this->keywordValidator ? $this->keywordValidator->validate($context) : $this->doValidate($context);
        $context->popSharedObject();

        return $error;
    }

    
    public function doValidate(ValidationContext $context): ?ValidationError
    {
        if ($this->before && ($error = $this->applyKeywords($this->before, $context))) {
            return $error;
        }

        if ($this->types && ($type = $context->currentDataType())) {
            if (isset($this->types[$type]) && ($error = $this->applyKeywords($this->types[$type], $context))) {
                return $error;
            }

            if (($type = Helper::getJsonSuperType($type)) && isset($this->types[$type])) {
                if ($error = $this->applyKeywords($this->types[$type], $context)) {
                    return $error;
                }
            }

            unset($type);
        }

        if ($this->after && ($error = $this->applyKeywords($this->after, $context))) {
            return $error;
        }

        return null;
    }

    
    protected function applyKeywords(array $keywords, ValidationContext $context): ?ValidationError
    {
        if ($context->stopAtFirstError()) {
            foreach ($keywords as $keyword) {
                if ($error = $keyword->validate($context, $this)) {
                    return $error;
                }
            }
            return null;
        }

        
        $error_list = null;

        foreach ($keywords as $keyword) {
            if ($error = $keyword->validate($context, $this)) {
                $error_list ??= [];
                $error_list[] = $error;
            }
        }

        if (!$error_list) {
            return null;
        }

        if (count($error_list) === 1) {
            return $error_list[0];
        }

        return new ValidationError(
            '',
            $this,
            DataInfo::fromContext($context),
            'Data must match schema',
            [],
            $error_list
        );
    }
}