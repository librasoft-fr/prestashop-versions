<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\Serializer\RepresentationSerializerInterface;
use Sentry\Util\PHPConfiguration;


final class StacktraceBuilder
{
    
    private $frameBuilder;

    
    public function __construct(Options $options, RepresentationSerializerInterface $representationSerializer)
    {
        $this->frameBuilder = new FrameBuilder($options, $representationSerializer);

        if (PHPConfiguration::isBooleanIniOptionEnabled('zend.exception_ignore_args')) {
            $options->getLoggerOrNullLogger()->warning('The "zend.exception_ignore_args" PHP setting is enabled which results in missing stack trace arguments, see: https://docs.sentry.io/platforms/php/troubleshooting/#missing-variables-in-stack-traces.');
        }
    }

    
    public function buildFromException(\Throwable $exception): Stacktrace
    {
        return $this->buildFromBacktrace($exception->getTrace(), $exception->getFile(), $exception->getLine());
    }

    
    public function buildFromBacktrace(array $backtrace, string $file, int $line): Stacktrace
    {
        $frames = [];

        foreach ($backtrace as $backtraceFrame) {
            array_unshift($frames, $this->frameBuilder->buildFromBacktraceFrame($file, $line, $backtraceFrame));

            $file = $backtraceFrame['file'] ?? Frame::INTERNAL_FRAME_FILENAME;
            $line = $backtraceFrame['line'] ?? 0;
        }

        
        array_unshift($frames, $this->frameBuilder->buildFromBacktraceFrame($file, $line, []));

        return new Stacktrace($frames);
    }
}
