<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\Exception\FatalErrorException;
use Sentry\Exception\SilencedErrorException;


final class ErrorHandler
{
    
    public const DEFAULT_RESERVED_MEMORY_SIZE = 16 * 1024; 

    
    private const OOM_MESSAGE_MATCHER = '/^Allowed memory size of (?<memory_limit>\d+) bytes exhausted[^\r\n]* \(tried to allocate \d+ bytes\)/';

    
    private const PHP8_UNSILENCEABLE_FATAL_ERRORS = \E_ERROR | \E_PARSE | \E_CORE_ERROR | \E_COMPILE_ERROR | \E_USER_ERROR | \E_RECOVERABLE_ERROR;

    
    private static $handlerInstance;

    
    private $errorListeners = [];

    
    private $fatalErrorListeners = [];

    
    private $exceptionListeners = [];

    
    private $exceptionReflection;

    
    private $previousErrorHandler;

    
    private $previousExceptionHandler;

    
    private $isErrorHandlerRegistered = false;

    
    private $isExceptionHandlerRegistered = false;

    
    private $isFatalErrorHandlerRegistered = false;

    
    private $memoryLimitIncreaseOnOutOfMemoryErrorValue = 5 * 1024 * 1024; 

    
    private $options;

    
    private static $didIncreaseMemoryLimit = false;

    
    private static $reservedMemory;

    
    private static $disableFatalErrorHandler = false;

    
    private const ERROR_LEVELS_DESCRIPTION = [
        \E_DEPRECATED => 'Deprecated',
        \E_USER_DEPRECATED => 'User Deprecated',
        \E_NOTICE => 'Notice',
        \E_USER_NOTICE => 'User Notice',
        
        2048 => 'Runtime Notice',
        \E_WARNING => 'Warning',
        \E_USER_WARNING => 'User Warning',
        \E_COMPILE_WARNING => 'Compile Warning',
        \E_CORE_WARNING => 'Core Warning',
        \E_USER_ERROR => 'User Error',
        \E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        \E_COMPILE_ERROR => 'Compile Error',
        \E_PARSE => 'Parse Error',
        \E_ERROR => 'Error',
        \E_CORE_ERROR => 'Core Error',
    ];

    
    private function __construct()
    {
        $this->exceptionReflection = new \ReflectionProperty(\Exception::class, 'trace');
        if (\PHP_VERSION_ID < 80100) {
            $this->exceptionReflection->setAccessible(true);
        }
    }

    
    public static function registerOnceErrorHandler(?Options $options = null): self
    {
        if (self::$handlerInstance === null) {
            self::$handlerInstance = new self();
        }

        self::$handlerInstance->options = $options;

        if (self::$handlerInstance->isErrorHandlerRegistered) {
            return self::$handlerInstance;
        }

        $errorHandlerCallback = \Closure::fromCallable([self::$handlerInstance, 'handleError']);

        self::$handlerInstance->isErrorHandlerRegistered = true;
        self::$handlerInstance->previousErrorHandler = set_error_handler($errorHandlerCallback);

        if (self::$handlerInstance->previousErrorHandler === null) {
            restore_error_handler();

            
            
            
            
            set_error_handler($errorHandlerCallback, \E_ALL);
        }

        return self::$handlerInstance;
    }

    
    public static function registerOnceFatalErrorHandler(int $reservedMemorySize = self::DEFAULT_RESERVED_MEMORY_SIZE): self
    {
        if ($reservedMemorySize <= 0) {
            throw new \InvalidArgumentException('The $reservedMemorySize argument must be greater than 0.');
        }

        if (self::$handlerInstance === null) {
            self::$handlerInstance = new self();
        }

        if (self::$handlerInstance->isFatalErrorHandlerRegistered) {
            return self::$handlerInstance;
        }

        self::$handlerInstance->isFatalErrorHandlerRegistered = true;
        self::$reservedMemory = str_repeat('x', $reservedMemorySize);

        register_shutdown_function(\Closure::fromCallable([self::$handlerInstance, 'handleFatalError']));

        return self::$handlerInstance;
    }

    
    public static function registerOnceExceptionHandler(): self
    {
        if (self::$handlerInstance === null) {
            self::$handlerInstance = new self();
        }

        if (self::$handlerInstance->isExceptionHandlerRegistered) {
            return self::$handlerInstance;
        }

        self::$handlerInstance->isExceptionHandlerRegistered = true;
        self::$handlerInstance->previousExceptionHandler = set_exception_handler(\Closure::fromCallable([self::$handlerInstance, 'handleException']));

        return self::$handlerInstance;
    }

    
    public function addErrorHandlerListener(callable $listener): void
    {
        $this->errorListeners[] = $listener;
    }

    
    public function addFatalErrorHandlerListener(callable $listener): void
    {
        $this->fatalErrorListeners[] = $listener;
    }

    
    public function addExceptionHandlerListener(callable $listener): void
    {
        $this->exceptionListeners[] = $listener;
    }

    
    public function setMemoryLimitIncreaseOnOutOfMemoryErrorInBytes(?int $valueInBytes): void
    {
        if ($valueInBytes !== null && $valueInBytes <= 0) {
            throw new \InvalidArgumentException('The $valueInBytes argument must be greater than 0 or null.');
        }

        $this->memoryLimitIncreaseOnOutOfMemoryErrorValue = $valueInBytes;
    }

    
    private function handleError(int $level, string $message, string $file, int $line, ?array $errcontext = []): bool
    {
        $isSilencedError = error_reporting() === 0;

        if (\PHP_MAJOR_VERSION >= 8) {
            
            
            
            
            $isSilencedError = 0 === (error_reporting() & ~self::PHP8_UNSILENCEABLE_FATAL_ERRORS);

            
            
            
            if ($level === (self::PHP8_UNSILENCEABLE_FATAL_ERRORS & $level)) {
                $isSilencedError = false;
            }
        }

        if ($this->shouldHandleError($level, $isSilencedError)) {
            if ($isSilencedError) {
                $errorAsException = new SilencedErrorException(self::ERROR_LEVELS_DESCRIPTION[$level] . ': ' . $message, 0, $level, $file, $line);
            } else {
                $errorAsException = new \ErrorException(self::ERROR_LEVELS_DESCRIPTION[$level] . ': ' . $message, 0, $level, $file, $line);
            }

            $backtrace = $this->cleanBacktraceFromErrorHandlerFrames($errorAsException->getTrace(), $errorAsException->getFile(), $errorAsException->getLine());

            $this->exceptionReflection->setValue($errorAsException, $backtrace);

            $this->invokeListeners($this->errorListeners, $errorAsException);
        }

        if ($this->previousErrorHandler !== null) {
            return false !== ($this->previousErrorHandler)($level, $message, $file, $line, $errcontext);
        }

        return false;
    }

    private function shouldHandleError(int $level, bool $silenced): bool
    {
        
        if ($this->options === null) {
            return true;
        }

        if ($silenced) {
            return $this->options->shouldCaptureSilencedErrors();
        }

        return ($this->options->getErrorTypes() & $level) !== 0;
    }

    
    private function handleFatalError(): void
    {
        if (self::$disableFatalErrorHandler) {
            return;
        }

        
        self::$reservedMemory = null;

        $error = error_get_last();

        if (!empty($error) && $error['type'] & (\E_ERROR | \E_PARSE | \E_CORE_ERROR | \E_CORE_WARNING | \E_COMPILE_ERROR | \E_COMPILE_WARNING)) {
            
            if (self::$didIncreaseMemoryLimit === false
                && $this->memoryLimitIncreaseOnOutOfMemoryErrorValue !== null
                && preg_match(self::OOM_MESSAGE_MATCHER, $error['message'], $matches) === 1
            ) {
                $currentMemoryLimit = (int) $matches['memory_limit'];
                $newMemoryLimit = $currentMemoryLimit + $this->memoryLimitIncreaseOnOutOfMemoryErrorValue;

                
                
                
                
                if ($newMemoryLimit > memory_get_usage(true)) {
                    $this->setMemoryLimitWithoutHandlingWarnings($newMemoryLimit);
                }

                self::$didIncreaseMemoryLimit = true;
            }

            $errorAsException = new FatalErrorException(self::ERROR_LEVELS_DESCRIPTION[$error['type']] . ': ' . $error['message'], 0, $error['type'], $error['file'], $error['line']);

            $this->exceptionReflection->setValue($errorAsException, []);

            $this->invokeListeners($this->fatalErrorListeners, $errorAsException);
        }
    }

    
    private function handleException(\Throwable $exception): void
    {
        $this->invokeListeners($this->exceptionListeners, $exception);

        $previousExceptionHandlerException = $exception;

        
        
        $previousExceptionHandler = $this->previousExceptionHandler;
        $this->previousExceptionHandler = null;

        try {
            if ($previousExceptionHandler !== null) {
                $previousExceptionHandler($exception);

                return;
            }
        } catch (\Throwable $previousExceptionHandlerException) {
            
            
            
        }

        
        
        
        if ($exception === $previousExceptionHandlerException) {
            
            self::$disableFatalErrorHandler = true;

            throw $exception;
        }

        $this->handleException($previousExceptionHandlerException);
    }

    
    private function setMemoryLimitWithoutHandlingWarnings(int $memoryLimit): void
    {
        set_error_handler(static function (): bool {
            return true;
        }, \E_WARNING);

        try {
            ini_set('memory_limit', (string) $memoryLimit);
        } finally {
            restore_error_handler();
        }
    }

    
    private function cleanBacktraceFromErrorHandlerFrames(array $backtrace, string $file, int $line): array
    {
        $cleanedBacktrace = $backtrace;
        $index = 0;

        while ($index < \count($backtrace)) {
            if (isset($backtrace[$index]['file'], $backtrace[$index]['line']) && $backtrace[$index]['line'] === $line && $backtrace[$index]['file'] === $file) {
                $cleanedBacktrace = \array_slice($cleanedBacktrace, 1 + $index);

                break;
            }

            ++$index;
        }

        return $cleanedBacktrace;
    }

    
    private function invokeListeners(array $listeners, \Throwable $throwable): void
    {
        foreach ($listeners as $listener) {
            try {
                $listener($throwable);
            } catch (\Throwable $exception) {
                
            }
        }
    }
}
