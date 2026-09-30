<?php

declare(strict_types=1);

namespace Sentry\Tracing;

use Sentry\Options;
use Sentry\State\HubInterface;
use Sentry\State\Scope;


final class DynamicSamplingContext
{
    private const SENTRY_ENTRY_PREFIX = 'sentry-';

    
    private $entries = [];

    
    private $isFrozen = false;

    
    private function __construct()
    {
    }

    
    public function set(string $key, string $value, bool $forceOverwrite = false): self
    {
        if ($this->isFrozen && !$forceOverwrite) {
            return $this;
        }

        $this->entries[$key] = $value;

        return $this;
    }

    
    public function has(string $key): bool
    {
        return isset($this->entries[$key]);
    }

    
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->entries[$key] ?? $default;
    }

    
    public function freeze(): self
    {
        $this->isFrozen = true;

        return $this;
    }

    
    public function isFrozen(): bool
    {
        return $this->isFrozen;
    }

    
    public function hasEntries(): bool
    {
        return !empty($this->entries);
    }

    
    public function getEntries(): array
    {
        return $this->entries;
    }

    
    public static function fromHeader(string $header): self
    {
        $samplingContext = new self();

        foreach (explode(',', $header) as $listMember) {
            if (empty(trim($listMember))) {
                continue;
            }

            $keyValueAndProperties = explode(';', $listMember, 2);
            $keyValue = trim($keyValueAndProperties[0]);

            if (!str_contains($keyValue, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $keyValue, 2);

            if (mb_substr($key, 0, mb_strlen(self::SENTRY_ENTRY_PREFIX)) === self::SENTRY_ENTRY_PREFIX) {
                $samplingContext->set(rawurldecode(mb_substr($key, mb_strlen(self::SENTRY_ENTRY_PREFIX))), rawurldecode($value));
            }
        }

        
        
        
        $samplingContext->isFrozen = $samplingContext->hasEntries();

        return $samplingContext;
    }

    
    public static function fromTransaction(Transaction $transaction, HubInterface $hub): self
    {
        $samplingContext = new self();
        $samplingContext->set('trace_id', (string) $transaction->getTraceId());

        $sampleRate = $transaction->getMetaData()->getSamplingRate();
        if ($sampleRate !== null) {
            $samplingContext->set('sample_rate', (string) $sampleRate);
        }

        
        if ($transaction->getMetadata()->getSource() !== TransactionSource::url()) {
            $samplingContext->set('transaction', $transaction->getName());
        }

        $client = $hub->getClient();

        if ($client !== null) {
            self::setOrgOptions($client->getOptions(), $samplingContext);
        }

        if ($transaction->getSampled() !== null) {
            $samplingContext->set('sampled', $transaction->getSampled() ? 'true' : 'false');
        }

        if ($transaction->getMetadata()->getSampleRand() !== null) {
            $samplingContext->set('sample_rand', (string) $transaction->getMetadata()->getSampleRand());
        }

        $samplingContext->freeze();

        return $samplingContext;
    }

    public static function fromOptions(Options $options, Scope $scope): self
    {
        $samplingContext = new self();
        $samplingContext->set('trace_id', (string) $scope->getPropagationContext()->getTraceId());
        $samplingContext->set('sample_rand', (string) $scope->getPropagationContext()->getSampleRand());

        if ($options->getTracesSampleRate() !== null) {
            $samplingContext->set('sample_rate', (string) $options->getTracesSampleRate());
        }

        self::setOrgOptions($options, $samplingContext);

        $samplingContext->freeze();

        return $samplingContext;
    }

    private static function setOrgOptions(Options $options, DynamicSamplingContext $samplingContext): void
    {
        if ($options->getDsn() !== null && $options->getDsn()->getPublicKey() !== null) {
            $samplingContext->set('public_key', $options->getDsn()->getPublicKey());
        }

        if ($options->getOrgId() !== null) {
            $samplingContext->set('org_id', (string) $options->getOrgId());
        } elseif ($options->getDsn() !== null && $options->getDsn()->getOrgId() !== null) {
            $samplingContext->set('org_id', (string) $options->getDsn()->getOrgId());
        }

        if ($options->getRelease() !== null) {
            $samplingContext->set('release', $options->getRelease());
        }

        if ($options->getEnvironment() !== null) {
            $samplingContext->set('environment', $options->getEnvironment());
        }
    }

    
    public function __toString(): string
    {
        $result = [];

        foreach ($this->entries as $key => $value) {
            $result[] = rawurlencode(self::SENTRY_ENTRY_PREFIX . $key) . '=' . rawurlencode($value);
        }

        return implode(',', $result);
    }
}
