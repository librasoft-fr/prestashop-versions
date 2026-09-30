<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

abstract class AbstractProcessingHandler extends AbstractHandler implements ProcessableHandlerInterface, FormattableHandlerInterface
{
    use ProcessableHandlerTrait;
    use FormattableHandlerTrait;

    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if ($this->processors) {
            $record = $this->processRecord($record);
        }

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }

    abstract protected function write(array $record): void;

    public function reset()
    {
        parent::reset();

        $this->resetProcessors();
    }
}
