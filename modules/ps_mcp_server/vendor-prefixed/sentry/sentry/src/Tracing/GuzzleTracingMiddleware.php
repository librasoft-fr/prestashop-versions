<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use PsMcpServerDeps\GuzzleHttp\Psr7\Uri;
use PsMcpServerDeps\Sentry\Breadcrumb;
use PsMcpServerDeps\Sentry\ClientInterface;
use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\State\HubInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function PsMcpServerDeps\Sentry\getBaggage;
use function PsMcpServerDeps\Sentry\getTraceparent;

final class GuzzleTracingMiddleware
{
    public static function trace(?HubInterface $hub = null): \Closure
    {
        return static function (callable $handler) use ($hub): \Closure {
            return static function (RequestInterface $request, array $options) use ($hub, $handler) {
                $hub = $hub ?? SentrySdk::getCurrentHub();
                $client = $hub->getClient();
                $parentSpan = $hub->getSpan();

                $partialUri = Uri::fromParts([
                    'scheme' => $request->getUri()->getScheme(),
                    'host' => $request->getUri()->getHost(),
                    'port' => $request->getUri()->getPort(),
                    'path' => $request->getUri()->getPath(),
                ]);

                $spanAndBreadcrumbData = [
                    'http.request.method' => $request->getMethod(),
                    'http.request.body.size' => $request->getBody()->getSize(),
                ];

                if ($request->getUri()->getQuery() !== '') {
                    $spanAndBreadcrumbData['http.query'] = $request->getUri()->getQuery();
                }
                if ($request->getUri()->getFragment() !== '') {
                    $spanAndBreadcrumbData['http.fragment'] = $request->getUri()->getFragment();
                }

                $childSpan = null;

                if ($parentSpan !== null && $parentSpan->getSampled()) {
                    $spanContext = new SpanContext();
                    $spanContext->setOp('http.client');
                    $spanContext->setData($spanAndBreadcrumbData);
                    $spanContext->setOrigin('auto.http.guzzle');
                    $spanContext->setDescription($request->getMethod() . ' ' . $partialUri);

                    $childSpan = $parentSpan->startChild($spanContext);

                    $hub->setSpan($childSpan);
                }

                if (self::shouldAttachTracingHeaders($client, $request)) {
                    $traceParent = getTraceparent();
                    if ($traceParent !== '') {
                        $request = $request->withHeader('sentry-trace', $traceParent);
                    }

                    $baggage = getBaggage();
                    if ($baggage !== '') {
                        $request = $request->withHeader('baggage', $baggage);
                    }
                }

                $handlerPromiseCallback = static function ($responseOrException) use ($hub, $spanAndBreadcrumbData, $childSpan, $parentSpan, $partialUri) {
                    if ($childSpan !== null) {
                        $childSpan->finish();

                        $hub->setSpan($parentSpan);
                    }

                    $response = null;

                    if ($responseOrException instanceof ResponseInterface) {
                        $response = $responseOrException;
                    } elseif ($responseOrException instanceof GuzzleRequestException) {
                        $response = $responseOrException->getResponse();
                    }

                    $breadcrumbLevel = Breadcrumb::LEVEL_INFO;

                    if ($response !== null) {
                        $spanAndBreadcrumbData['http.response.body.size'] = $response->getBody()->getSize();
                        $spanAndBreadcrumbData['http.response.status_code'] = $response->getStatusCode();

                        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
                            $breadcrumbLevel = Breadcrumb::LEVEL_WARNING;
                        } elseif ($response->getStatusCode() >= 500) {
                            $breadcrumbLevel = Breadcrumb::LEVEL_ERROR;
                        }
                    }

                    if ($childSpan !== null) {
                        if ($response !== null) {
                            $childSpan->setStatus(SpanStatus::createFromHttpStatusCode($response->getStatusCode()));
                            $childSpan->setData($spanAndBreadcrumbData);
                        } else {
                            $childSpan->setStatus(SpanStatus::internalError());
                        }
                    }

                    $hub->addBreadcrumb(new Breadcrumb(
                        $breadcrumbLevel,
                        Breadcrumb::TYPE_HTTP,
                        'http',
                        null,
                        array_merge([
                            'url' => (string) $partialUri,
                        ], $spanAndBreadcrumbData)
                    ));

                    if ($responseOrException instanceof \Throwable) {
                        throw $responseOrException;
                    }

                    return $responseOrException;
                };

                return $handler($request, $options)->then($handlerPromiseCallback, $handlerPromiseCallback);
            };
        };
    }

    private static function shouldAttachTracingHeaders(?ClientInterface $client, RequestInterface $request): bool
    {
        if ($client === null) {
            return false;
        }

        $sdkOptions = $client->getOptions();

        return $sdkOptions->getTracePropagationTargets() === null
               || \in_array($request->getUri()->getHost(), $sdkOptions->getTracePropagationTargets());
    }
}
