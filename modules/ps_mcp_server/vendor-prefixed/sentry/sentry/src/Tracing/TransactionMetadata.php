<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

final class TransactionMetadata
{
    private $samplingRate;

    private $dynamicSamplingContext;

    private $source;

    private $parentSamplingRate;

    private $sampleRand;

    public function __construct(
        $samplingRate = null,
        ?DynamicSamplingContext $dynamicSamplingContext = null,
        ?TransactionSource $source = null,
        ?float $parentSamplingRate = null,
        ?float $sampleRand = null,
    ) {
        $this->samplingRate = $samplingRate;
        $this->dynamicSamplingContext = $dynamicSamplingContext;
        $this->source = $source ?? TransactionSource::custom();
        $this->parentSamplingRate = $parentSamplingRate;
        $this->sampleRand = $sampleRand ?? round(mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax(), 6);
    }

    public function getSamplingRate()
    {
        return $this->samplingRate;
    }

    public function setSamplingRate($samplingRate): self
    {
        $this->samplingRate = $samplingRate;

        return $this;
    }

    public function getParentSamplingRate(): ?float
    {
        return $this->parentSamplingRate;
    }

    public function setParentSamplingRate(?float $parentSamplingRate): self
    {
        $this->parentSamplingRate = $parentSamplingRate;

        return $this;
    }

    public function getSampleRand(): ?float
    {
        return $this->sampleRand;
    }

    public function setSampleRand(?float $sampleRand): self
    {
        $this->sampleRand = $sampleRand;

        return $this;
    }

    public function getDynamicSamplingContext(): ?DynamicSamplingContext
    {
        return $this->dynamicSamplingContext;
    }

    public function setDynamicSamplingContext(?DynamicSamplingContext $dynamicSamplingContext): self
    {
        $this->dynamicSamplingContext = $dynamicSamplingContext;

        return $this;
    }

    public function getSource(): ?TransactionSource
    {
        return $this->source;
    }

    public function setSource(?TransactionSource $source): self
    {
        $this->source = $source;

        return $this;
    }
}
