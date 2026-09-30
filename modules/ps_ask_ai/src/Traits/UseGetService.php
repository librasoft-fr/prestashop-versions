<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait UseGetService
{
    private function getService(string $serviceName): mixed
    {
        $container = $this->getSymfonyContainer();

        if (null !== $container) {
            return $container->has($serviceName)/* && $container->initialized($serviceName) */
                ? $container->get($serviceName, ContainerInterface::NULL_ON_INVALID_REFERENCE)
                : null;
        }

        return null;
    }

    private function hasService(string $serviceName): mixed
    {
        $container = $this->getSymfonyContainer();

        if (null !== $container) {
            return $container->has($serviceName);
        }

        return null;
    }

    private function getSymfonyContainer(): ?ContainerInterface
    {
        if (null === SymfonyContainer::getInstance()) {
            return null;
        }

        return SymfonyContainer::getInstance();
    }
}
