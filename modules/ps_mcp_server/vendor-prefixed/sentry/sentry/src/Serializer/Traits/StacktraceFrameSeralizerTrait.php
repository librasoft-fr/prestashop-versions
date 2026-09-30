<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer\Traits;

use PsMcpServerDeps\Sentry\Frame;

trait StacktraceFrameSeralizerTrait
{
    protected static function serializeStacktraceFrame(Frame $frame): array
    {
        $result = [
            'filename' => $frame->getFile(),
            'lineno' => $frame->getLine(),
            'in_app' => $frame->isInApp(),
        ];

        if ($frame->getAbsoluteFilePath() !== null) {
            $result['abs_path'] = $frame->getAbsoluteFilePath();
        }

        if ($frame->getFunctionName() !== null) {
            $result['function'] = $frame->getFunctionName();
        }

        if ($frame->getRawFunctionName() !== null) {
            $result['raw_function'] = $frame->getRawFunctionName();
        }

        if (!empty($frame->getPreContext())) {
            $result['pre_context'] = $frame->getPreContext();
        }

        if ($frame->getContextLine() !== null) {
            $result['context_line'] = $frame->getContextLine();
        }

        if (!empty($frame->getPostContext())) {
            $result['post_context'] = $frame->getPostContext();
        }

        if (!empty($frame->getVars())) {
            $result['vars'] = $frame->getVars();
        }

        return $result;
    }
}
