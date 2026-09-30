<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

use PsMcpServerDeps\Sentry\Serializer\RepresentationSerializerInterface;
use PsMcpServerDeps\Sentry\Util\PrefixStripper;

final class FrameBuilder
{
    use PrefixStripper;

    private $options;

    private $representationSerializer;

    public function __construct(Options $options, RepresentationSerializerInterface $representationSerializer)
    {
        $this->options = $options;
        $this->representationSerializer = $representationSerializer;
    }

    public function buildFromBacktraceFrame(string $file, int $line, array $backtraceFrame): Frame
    {
        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d code|runtime-created function)$/', $file, $matches)) {
            $file = $matches[1];
            $line = (int) $matches[2];
        }

        $functionName = null;
        $rawFunctionName = null;
        $strippedFilePath = $this->stripPrefixFromFilePath($this->options, $file);

        if (isset($backtraceFrame['class']) && isset($backtraceFrame['function'])) {
            $functionName = $backtraceFrame['class'];

            if ($this->options->getPrefixes()) {
                $prefixStrippedFunctionName = preg_replace_callback('/@anonymous\\x00([^:]+)(:.*)?/', function (array $matches) {
                    return "@anonymous\x00" . $this->stripPrefixFromFilePath($this->options, $matches[1]) . ($matches[2] ?? '');
                }, $functionName);

                if ($prefixStrippedFunctionName) {
                    $functionName = $prefixStrippedFunctionName;
                }
            }

            $rawFunctionName = \sprintf('%s::%s', $backtraceFrame['class'], $backtraceFrame['function']);
            $functionName = \sprintf('%s::%s', preg_replace('/(?::\d+\$|0x)[a-fA-F0-9]+$/', '', $functionName), $backtraceFrame['function']);
        } elseif (isset($backtraceFrame['function'])) {
            $functionName = $backtraceFrame['function'];
        }

        if (\PHP_VERSION_ID >= 80400 && $functionName !== null && $this->options->getPrefixes()) {
            $prefixStrippedFunctionName = preg_replace_callback('/^\{closure:(.*?):(\d+)}$/', function (array $matches) {
                return '{closure:' . $this->stripPrefixFromFilePath($this->options, $matches[1]) . ':' . $matches[2] . '}';
            }, $functionName);

            if ($prefixStrippedFunctionName) {
                $functionName = $prefixStrippedFunctionName;
            }
        }

        return new Frame(
            $functionName,
            $strippedFilePath,
            $line,
            $rawFunctionName,
            $file !== Frame::INTERNAL_FRAME_FILENAME ? $file : null,
            $this->getFunctionArguments($backtraceFrame),
            $this->isFrameInApp($file, $functionName)
        );
    }

    private function isFrameInApp(string $file, ?string $functionName): bool
    {
        if ($file === Frame::INTERNAL_FRAME_FILENAME) {
            return false;
        }

        if ($functionName !== null && substr($functionName, 0, \strlen('PsMcpServerDeps\\Sentry\\')) === 'PsMcpServerDeps\\Sentry\\') {
            return false;
        }

        $excludedAppPaths = $this->options->getInAppExcludedPaths();
        $includedAppPaths = $this->options->getInAppIncludedPaths();
        $absoluteFilePath = @realpath($file) ?: $file;
        $isInApp = true;

        foreach ($excludedAppPaths as $excludedAppPath) {
            if (mb_substr($absoluteFilePath, 0, mb_strlen($excludedAppPath)) === $excludedAppPath) {
                $isInApp = false;

                break;
            }
        }

        foreach ($includedAppPaths as $includedAppPath) {
            if (mb_substr($absoluteFilePath, 0, mb_strlen($includedAppPath)) === $includedAppPath) {
                $isInApp = true;

                break;
            }
        }

        return $isInApp;
    }

    private function getFunctionArguments(array $backtraceFrame): array
    {
        if (!isset($backtraceFrame['function'], $backtraceFrame['args'])) {
            return [];
        }

        $reflectionFunction = null;

        try {
            if (isset($backtraceFrame['class'])) {
                if (method_exists($backtraceFrame['class'], $backtraceFrame['function'])) {
                    $reflectionFunction = new \ReflectionMethod($backtraceFrame['class'], $backtraceFrame['function']);
                } elseif (isset($backtraceFrame['type']) && $backtraceFrame['type'] === '::') {
                    $reflectionFunction = new \ReflectionMethod($backtraceFrame['class'], '__callStatic');
                } else {
                    $reflectionFunction = new \ReflectionMethod($backtraceFrame['class'], '__call');
                }
            } elseif ($backtraceFrame['function'] !== '__lambda_func' && !str_starts_with($backtraceFrame['function'], '{closure') && \function_exists($backtraceFrame['function'])) {
                $reflectionFunction = new \ReflectionFunction($backtraceFrame['function']);
            }
        } catch (\ReflectionException $e) {
        }

        $argumentValues = [];

        if ($reflectionFunction !== null) {
            $argumentValues = $this->getFunctionArgumentValues($reflectionFunction, $backtraceFrame['args']);
        } else {
            foreach ($backtraceFrame['args'] as $parameterPosition => $parameterValue) {
                $argumentValues['param' . $parameterPosition] = $parameterValue;
            }
        }

        foreach ($argumentValues as $argumentName => $argumentValue) {
            $argumentValues[$argumentName] = $this->representationSerializer->representationSerialize($argumentValue);
        }

        return $argumentValues;
    }

    private function getFunctionArgumentValues(\ReflectionFunctionAbstract $reflectionFunction, array $backtraceFrameArgs): array
    {
        $argumentValues = [];

        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            $parameterPosition = $reflectionParameter->getPosition();

            if ($reflectionParameter->isVariadic()) {
                $variadicArgs = \array_slice($backtraceFrameArgs, $parameterPosition);
                $argumentValues[$reflectionParameter->getName()] = array_values($variadicArgs);

                break;
            }

            if (!\array_key_exists($parameterPosition, $backtraceFrameArgs)) {
                continue;
            }

            $argumentValues[$reflectionParameter->getName()] = $backtraceFrameArgs[$parameterPosition];
        }

        return $argumentValues;
    }
}
