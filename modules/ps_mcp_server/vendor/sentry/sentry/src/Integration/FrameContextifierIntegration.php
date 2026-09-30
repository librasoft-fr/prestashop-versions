<?php

declare(strict_types=1);

namespace Sentry\Integration;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sentry\Event;
use Sentry\Frame;
use Sentry\SentrySdk;
use Sentry\Stacktrace;
use Sentry\State\Scope;


final class FrameContextifierIntegration implements IntegrationInterface
{
    
    private $logger;

    
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(static function (Event $event): Event {
            $client = SentrySdk::getCurrentHub()->getClient();

            if ($client === null) {
                return $event;
            }

            $maxContextLines = $client->getOptions()->getContextLines();
            $integration = $client->getIntegration(self::class);

            if ($integration === null || $maxContextLines === null) {
                return $event;
            }

            $stacktrace = $event->getStacktrace();

            if ($stacktrace !== null) {
                $integration->addContextToStacktraceFrames($maxContextLines, $stacktrace);
            }

            foreach ($event->getExceptions() as $exception) {
                if ($exception->getStacktrace() !== null) {
                    $integration->addContextToStacktraceFrames($maxContextLines, $exception->getStacktrace());
                }
            }

            return $event;
        });
    }

    
    private function addContextToStacktraceFrames(int $maxContextLines, Stacktrace $stacktrace): void
    {
        foreach ($stacktrace->getFrames() as $frame) {
            if ($frame->isInternal()) {
                continue;
            }

            $this->addContextToStacktraceFrame($maxContextLines, $frame);
        }
    }

    
    private function addContextToStacktraceFrame(int $maxContextLines, Frame $frame): void
    {
        if ($frame->getAbsoluteFilePath() === null) {
            return;
        }

        $sourceCodeExcerpt = $this->getSourceCodeExcerpt($maxContextLines, $frame->getAbsoluteFilePath(), $frame->getLine());

        $frame->setPreContext($sourceCodeExcerpt['pre_context']);
        $frame->setContextLine($sourceCodeExcerpt['context_line']);
        $frame->setPostContext($sourceCodeExcerpt['post_context']);
    }

    
    private function getSourceCodeExcerpt(int $maxContextLines, string $filePath, int $lineNumber): array
    {
        $frame = [
            'pre_context' => [],
            'context_line' => null,
            'post_context' => [],
        ];

        $target = max(0, $lineNumber - ($maxContextLines + 1));
        $currentLineNumber = $target + 1;

        try {
            $file = new \SplFileObject($filePath);
            $file->seek($target);

            while (!$file->eof()) {
                
                $line = $file->current();
                $line = rtrim($line, "\r\n");

                if ($currentLineNumber === $lineNumber) {
                    $frame['context_line'] = $line;
                } elseif ($currentLineNumber < $lineNumber) {
                    $frame['pre_context'][] = $line;
                } elseif ($currentLineNumber > $lineNumber) {
                    $frame['post_context'][] = $line;
                }

                ++$currentLineNumber;

                if ($currentLineNumber > $lineNumber + $maxContextLines) {
                    break;
                }

                $file->next();
            }
        } catch (\Throwable $exception) {
            $this->logger->warning(
                \sprintf('Failed to get the source code excerpt for the file "%s".', $filePath),
                ['exception' => $exception]
            );
        }

        return $frame;
    }
}
