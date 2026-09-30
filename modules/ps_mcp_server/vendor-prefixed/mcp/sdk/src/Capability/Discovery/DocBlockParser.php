<?php

namespace PsMcpServerDeps\Mcp\Capability\Discovery;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Param;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlockFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlockFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DocBlockParser
{
    private DocBlockFactoryInterface $docBlockFactory;

    public function __construct(
        ?DocBlockFactoryInterface $docBlockFactory = null,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
    }

    public function parseDocBlock(string|false|null $docComment): ?DocBlock
    {
        if (false === $docComment || null === $docComment || empty($docComment)) {
            return null;
        }
        try {
            return $this->docBlockFactory->create($docComment);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to parse DocBlock', [
                'error' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function getDescription(?DocBlock $docBlock): ?string
    {
        if (!$docBlock) {
            return null;
        }
        $summary = trim($docBlock->getSummary());
        $descriptionBody = trim((string) $docBlock->getDescription());

        if ($summary && $descriptionBody) {
            return $summary . "\n\n" . $descriptionBody;
        }
        if ($summary) {
            return $summary;
        }
        if ($descriptionBody) {
            return $descriptionBody;
        }

        return null;
    }

    public function getParamTags(?DocBlock $docBlock): array
    {
        if (!$docBlock) {
            return [];
        }

        $paramTags = [];
        foreach ($docBlock->getTagsByName('param') as $tag) {
            if ($tag instanceof Param && $tag->getVariableName()) {
                $paramTags['$' . $tag->getVariableName()] = $tag;
            }
        }

        return $paramTags;
    }

    public function getParamDescription(?Param $paramTag): ?string
    {
        return $paramTag ? (trim((string) $paramTag->getDescription()) ?: null) : null;
    }

    public function getParamTypeString(?Param $paramTag): ?string
    {
        if ($paramTag && $paramTag->getType()) {
            $typeFromTag = trim((string) $paramTag->getType());
            if (!empty($typeFromTag)) {
                return ltrim($typeFromTag, '\\');
            }
        }

        return null;
    }
}
