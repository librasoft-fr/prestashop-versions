<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing\Traits;

use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\Tracing\DynamicSamplingContext;
use PsMcpServerDeps\Sentry\Tracing\SpanId;
use PsMcpServerDeps\Sentry\Tracing\TraceId;

trait TraceHeaderParserTrait
{
    private static $sentryTraceparentHeaderRegex = '/^[ \\t]*(?<trace_id>[0-9a-f]{32})?-?(?<span_id>[0-9a-f]{16})?-?(?<sampled>[01])?[ \\t]*$/i';

    protected static function parseTraceAndBaggageHeaders(string $sentryTrace, string $baggage): array
    {
        $result = [
            'traceId' => null,
            'parentSpanId' => null,
            'parentSampled' => null,
            'dynamicSamplingContext' => null,
            'sampleRand' => null,
            'parentSamplingRate' => null,
        ];

        $hasSentryTrace = false;

        if (preg_match(self::$sentryTraceparentHeaderRegex, $sentryTrace, $matches)) {
            if (!empty($matches['trace_id'])) {
                $result['traceId'] = new TraceId($matches['trace_id']);
                $hasSentryTrace = true;
            }

            if (!empty($matches['span_id'])) {
                $result['parentSpanId'] = new SpanId($matches['span_id']);
                $hasSentryTrace = true;
            }

            if (isset($matches['sampled'])) {
                $result['parentSampled'] = $matches['sampled'] === '1';
                $hasSentryTrace = true;
            }
        }

        $samplingContext = DynamicSamplingContext::fromHeader($baggage);

        if ($hasSentryTrace && !self::shouldContinueTrace($samplingContext)) {
            $result['traceId'] = null;
            $result['parentSpanId'] = null;
            $result['parentSampled'] = null;

            return $result;
        }

        if ($hasSentryTrace) {
            if (!$samplingContext->hasEntries()) {
                $samplingContext->freeze();
            }

            $result['dynamicSamplingContext'] = $samplingContext;

            if ($samplingContext->has('sample_rate')) {
                $result['parentSamplingRate'] = (float) $samplingContext->get('sample_rate');
            }
        }

        if ($hasSentryTrace) {
            $incomingSampleRand = self::parseSampleRand($samplingContext);
            if ($incomingSampleRand !== null) {
                $result['sampleRand'] = $incomingSampleRand;
            } elseif ($samplingContext->has('sample_rate') && $result['parentSampled'] !== null) {
                if ($result['parentSampled'] === true) {
                    $result['sampleRand'] = round(mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax() * (float) $samplingContext->get('sample_rate'), 6);
                } else {
                    $result['sampleRand'] = round(mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax() * (1 - (float) $samplingContext->get('sample_rate')) + (float) $samplingContext->get('sample_rate'), 6);
                }
            } elseif ($result['parentSampled'] !== null) {
                $result['sampleRand'] = round(mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax(), 6);
            }
        }

        return $result;
    }

    private static function parseSampleRand(DynamicSamplingContext $samplingContext): ?float
    {
        $sampleRand = $samplingContext->get('sample_rand');
        if ($sampleRand === null) {
            return null;
        }

        if (is_numeric($sampleRand)) {
            $sampleRandAsFloat = (float) $sampleRand;
            if ($sampleRandAsFloat >= 0.0 && $sampleRandAsFloat < 1.0) {
                return $sampleRandAsFloat;
            }
        }

        $hub = SentrySdk::getCurrentHub();
        $client = $hub->getClient();
        if ($client !== null) {
            $client->getOptions()->getLoggerOrNullLogger()->debug(
                'Ignoring invalid sentry-sample_rand baggage value because it must be a numeric value in the range [0, 1).',
                ['sample_rand' => $sampleRand]
            );
        }

        return null;
    }

    private static function shouldContinueTrace(DynamicSamplingContext $samplingContext): bool
    {
        $hub = SentrySdk::getCurrentHub();
        $client = $hub->getClient();

        if ($client === null) {
            return true;
        }

        $options = $client->getOptions();
        $clientOrgId = $options->getOrgId();
        if ($clientOrgId === null && $options->getDsn() !== null) {
            $clientOrgId = $options->getDsn()->getOrgId();
        }

        $baggageOrgId = $samplingContext->get('org_id');
        $logger = $options->getLoggerOrNullLogger();

        if ($clientOrgId !== null && $baggageOrgId !== null && ((string) $clientOrgId !== $baggageOrgId)) {
            $logger->debug(
                \sprintf(
                    "Starting a new trace because org IDs don't match (incoming baggage org_id: %s, SDK org_id: %s)",
                    $baggageOrgId,
                    $clientOrgId
                )
            );

            return false;
        }

        if ($options->isStrictTraceContinuationEnabled() && ($clientOrgId === null) !== ($baggageOrgId === null)) {
            $logger->debug(
                \sprintf(
                    'Starting a new trace because strict trace continuation is enabled and one org ID is missing (incoming baggage org_id: %s, SDK org_id: %s)',
                    $baggageOrgId !== null ? $baggageOrgId : 'none',
                    $clientOrgId !== null ? (string) $clientOrgId : 'none'
                )
            );

            return false;
        }

        return true;
    }
}
