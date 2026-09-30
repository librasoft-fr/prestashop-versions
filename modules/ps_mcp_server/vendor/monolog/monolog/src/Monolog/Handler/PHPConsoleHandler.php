<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Utils;
use PhpConsole\Connector;
use PhpConsole\Handler as VendorPhpConsoleHandler;
use PhpConsole\Helper;


class PHPConsoleHandler extends AbstractProcessingHandler
{
    
    private $options = [
        'enabled' => true, 
        'classesPartialsTraceIgnore' => ['Monolog\\'], 
        'debugTagsKeysInContext' => [0, 'tag'], 
        'useOwnErrorsHandler' => false, 
        'useOwnExceptionsHandler' => false, 
        'sourcesBasePath' => null, 
        'registerHelper' => true, 
        'serverEncoding' => null, 
        'headersLimit' => null, 
        'password' => null, 
        'enableSslOnlyMode' => false, 
        'ipMasks' => [], 
        'enableEvalListener' => false, 
        'dumperDetectCallbacks' => false, 
        'dumperLevelLimit' => 5, 
        'dumperItemsCountLimit' => 100, 
        'dumperItemSizeLimit' => 5000, 
        'dumperDumpSizeLimit' => 500000, 
        'detectDumpTraceAndSource' => false, 
        'dataStorage' => null, 
    ];

    
    private $connector;

    
    public function __construct(array $options = [], ?Connector $connector = null, $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!class_exists('PhpConsole\Connector')) {
            throw new \RuntimeException('PHP Console library not found. See https://github.com/barbushin/php-console#installation');
        }
        parent::__construct($level, $bubble);
        $this->options = $this->initOptions($options);
        $this->connector = $this->initConnector($connector);
    }

    
    private function initOptions(array $options): array
    {
        $wrongOptions = array_diff(array_keys($options), array_keys($this->options));
        if ($wrongOptions) {
            throw new \RuntimeException('Unknown options: ' . implode(', ', $wrongOptions));
        }

        return array_replace($this->options, $options);
    }

    private function initConnector(?Connector $connector = null): Connector
    {
        if (!$connector) {
            if ($this->options['dataStorage']) {
                Connector::setPostponeStorage($this->options['dataStorage']);
            }
            $connector = Connector::getInstance();
        }

        if ($this->options['registerHelper'] && !Helper::isRegistered()) {
            Helper::register();
        }

        if ($this->options['enabled'] && $connector->isActiveClient()) {
            if ($this->options['useOwnErrorsHandler'] || $this->options['useOwnExceptionsHandler']) {
                $handler = VendorPhpConsoleHandler::getInstance();
                $handler->setHandleErrors($this->options['useOwnErrorsHandler']);
                $handler->setHandleExceptions($this->options['useOwnExceptionsHandler']);
                $handler->start();
            }
            if ($this->options['sourcesBasePath']) {
                $connector->setSourcesBasePath($this->options['sourcesBasePath']);
            }
            if ($this->options['serverEncoding']) {
                $connector->setServerEncoding($this->options['serverEncoding']);
            }
            if ($this->options['password']) {
                $connector->setPassword($this->options['password']);
            }
            if ($this->options['enableSslOnlyMode']) {
                $connector->enableSslOnlyMode();
            }
            if ($this->options['ipMasks']) {
                $connector->setAllowedIpMasks($this->options['ipMasks']);
            }
            if ($this->options['headersLimit']) {
                $connector->setHeadersLimit($this->options['headersLimit']);
            }
            if ($this->options['detectDumpTraceAndSource']) {
                $connector->getDebugDispatcher()->detectTraceAndSource = true;
            }
            $dumper = $connector->getDumper();
            $dumper->levelLimit = $this->options['dumperLevelLimit'];
            $dumper->itemsCountLimit = $this->options['dumperItemsCountLimit'];
            $dumper->itemSizeLimit = $this->options['dumperItemSizeLimit'];
            $dumper->dumpSizeLimit = $this->options['dumperDumpSizeLimit'];
            $dumper->detectCallbacks = $this->options['dumperDetectCallbacks'];
            if ($this->options['enableEvalListener']) {
                $connector->startEvalRequestsListener();
            }
        }

        return $connector;
    }

    public function getConnector(): Connector
    {
        return $this->connector;
    }

    
    public function getOptions(): array
    {
        return $this->options;
    }

    public function handle(array $record): bool
    {
        if ($this->options['enabled'] && $this->connector->isActiveClient()) {
            return parent::handle($record);
        }

        return !$this->bubble;
    }

    
    protected function write(array $record): void
    {
        if ($record['level'] < Logger::NOTICE) {
            $this->handleDebugRecord($record);
        } elseif (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $this->handleExceptionRecord($record);
        } else {
            $this->handleErrorRecord($record);
        }
    }

    
    private function handleDebugRecord(array $record): void
    {
        $tags = $this->getRecordTags($record);
        $message = $record['message'];
        if ($record['context']) {
            $message .= ' ' . Utils::jsonEncode($this->connector->getDumper()->dump(array_filter($record['context'])), null, true);
        }
        $this->connector->getDebugDispatcher()->dispatchDebug($message, $tags, $this->options['classesPartialsTraceIgnore']);
    }

    
    private function handleExceptionRecord(array $record): void
    {
        $this->connector->getErrorsDispatcher()->dispatchException($record['context']['exception']);
    }

    
    private function handleErrorRecord(array $record): void
    {
        $context = $record['context'];

        $this->connector->getErrorsDispatcher()->dispatchError(
            $context['code'] ?? null,
            $context['message'] ?? $record['message'],
            $context['file'] ?? null,
            $context['line'] ?? null,
            $this->options['classesPartialsTraceIgnore']
        );
    }

    
    private function getRecordTags(array &$record)
    {
        $tags = null;
        if (!empty($record['context'])) {
            $context = & $record['context'];
            foreach ($this->options['debugTagsKeysInContext'] as $key) {
                if (!empty($context[$key])) {
                    $tags = $context[$key];
                    if ($key === 0) {
                        array_shift($context);
                    } else {
                        unset($context[$key]);
                    }
                    break;
                }
            }
        }

        return $tags ?: strtolower($record['level_name']);
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter('%message%');
    }
}
