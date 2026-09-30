<?php

declare(strict_types=1);

namespace Sentry\Tracing;

final class SamplingContext
{
    
    private $transactionContext;

    
    private $parentSampled;

    
    private $sampleRand;

    
    private $additionalContext;

    
    public static function getDefault(TransactionContext $transactionContext): self
    {
        $context = new self();
        $context->transactionContext = $transactionContext;
        $context->parentSampled = $transactionContext->getParentSampled();
        $context->sampleRand = $transactionContext->getMetadata()->getSampleRand();

        return $context;
    }

    public function getTransactionContext(): ?TransactionContext
    {
        return $this->transactionContext;
    }

    
    public function getParentSampled(): ?bool
    {
        return $this->parentSampled;
    }

    public function getSampleRand(): ?float
    {
        return $this->sampleRand;
    }

    
    public function setParentSampled(?bool $parentSampled): self
    {
        $this->parentSampled = $parentSampled;

        return $this;
    }

    
    public function setAdditionalContext(?array $additionalContext): self
    {
        $this->additionalContext = $additionalContext;

        return $this;
    }

    
    public function getAdditionalContext(): ?array
    {
        return $this->additionalContext;
    }
}
