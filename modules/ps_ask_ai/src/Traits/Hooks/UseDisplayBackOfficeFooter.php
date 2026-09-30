<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits\Hooks;

trait UseDisplayBackOfficeFooter
{
    /**
     * @var string[]
     */
    protected $backOfficeFooterCss = [];

    protected function addBackOfficeFooterCss(string $path): void
    {
        $this->backOfficeFooterCss[] = $path;
    }

    public function hookDisplayBackOfficeFooter(): string
    {
        if (empty($this->backOfficeFooterCss)) {
            return '';
        }

        $links = [];
        foreach (array_unique($this->backOfficeFooterCss) as $href) {
            $safeHref = htmlspecialchars($href, ENT_QUOTES);
            $links[] = "<link rel=\"stylesheet\" href=\"{$safeHref}\">";
        }

        return implode("\n", $links);
    }
}
