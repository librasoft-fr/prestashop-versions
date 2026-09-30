<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits\Hooks;

trait UseActionAdminControllerSetMedia
{
    use \PrestaShop\Module\PsAskAi\Traits\UseConstants;
    use \PrestaShop\Module\PsAskAi\Traits\UseGetService;

    /**
     * @return void
     */
    public function bootUseActionAdminControllerSetMedia(): void
    {
        $this->addAdminControllerMedia('loadMediaForAdminControllerSetMedia', 0);
    }

    /**
     * Hook actionAdminControllerSetMedia.
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        if (empty($this->adminControllerMediaMethods)) {
            return;
        }

        usort($this->adminControllerMediaMethods, function ($a, $b) {
            $order = $a['order'] < $b['order'] ? -1 : 1;

            return $a['order'] === $b['order'] ? 0 : $order;
        });
        foreach ($this->adminControllerMediaMethods as $setMediaMethod) {
            $this->{$setMediaMethod['method']}();
        }
    }

    /**
     * @param string $setMediaMethod The method to be called in the setMediaHook
     * @param int $order To ensure that a script is loaded before or after another one
     *
     * @return void
     */
    protected function addAdminControllerMedia(string $setMediaMethod, int $order = 1): void
    {
        if (!method_exists($this, $setMediaMethod)) {
            throw new \Exception("Method '{$setMediaMethod}' is not defined.");
        }
        $this->adminControllerMediaMethods[] = [
            'method' => $setMediaMethod,
            'order' => $order,
        ];
    }

    /**
     * Ajoute un timestamp à l'URL si la boutique est en mode debug.
     */
    private function addDebugTimestamp(string $url): string
    {
        // Si pas en mode debug, on ne touche à rien
        if (!defined('_PS_MODE_DEV_') || !_PS_MODE_DEV_) {
            return $url;
        }

        // On gère le cas où l'URL a déjà des query params
        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url . $separator . 'ts=' . time();
    }

    /**
     * Add JS and CSS file
     *
     * @return void
     */
    protected function loadMediaForAdminControllerSetMedia(): void
    {
        // {version} → module version: built CSS filename carries it (cache-bust).
        $this->addBackOfficeFooterCss(str_replace('{version}', $this->version, $_SERVER['PS_ASK_AI__FRONT_APP_CSS_URL']));
    }
}
