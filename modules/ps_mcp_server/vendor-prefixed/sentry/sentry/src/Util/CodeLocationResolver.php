<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

use PsMcpServerDeps\Sentry\Frame;
use PsMcpServerDeps\Sentry\FrameBuilder;
use PsMcpServerDeps\Sentry\Options;
use PsMcpServerDeps\Sentry\Serializer\RepresentationSerializerInterface;

final class CodeLocationResolver
{
    private $frameBuilder;

    public function __construct(Options $options, RepresentationSerializerInterface $representationSerializer)
    {
        $this->frameBuilder = new FrameBuilder($options, $representationSerializer);
    }

    public function resolve(int $limit = 20): ?array
    {
        $backtrace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $limit);

        return $this->resolveFromBacktrace($backtrace);
    }

    public function resolveFromBacktrace(array $backtrace): ?array
    {
        $frame = $this->findFirstInAppFrameForBacktrace($backtrace);

        if ($frame === null) {
            return null;
        }

        return $this->getCodeLocationForFrame($frame);
    }

    public function findFirstInAppFrameForBacktrace(array $backtrace): ?Frame
    {
        $file = Frame::INTERNAL_FRAME_FILENAME;
        $line = 0;

        foreach ($backtrace as $backtraceFrame) {
            $frame = $this->frameBuilder->buildFromBacktraceFrame($file, $line, $backtraceFrame);

            if ($frame->isInApp()) {
                return $frame;
            }

            $file = $backtraceFrame['file'] ?? Frame::INTERNAL_FRAME_FILENAME;
            $line = $backtraceFrame['line'] ?? 0;
        }

        return null;
    }

    public function getCodeLocationForFrame(Frame $frame): array
    {
        return [
            'code.filepath' => $frame->getFile(),
            'code.function' => $frame->getFunctionName(),
            'code.lineno' => $frame->getLine(),
        ];
    }
}
