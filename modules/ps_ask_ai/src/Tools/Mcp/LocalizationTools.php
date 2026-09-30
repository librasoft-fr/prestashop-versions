<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * This file is part of the PrestaShop Enterprise solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Tools\Mcp;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * MCP tools to read and manage the shop's languages and localisation packs.
 *
 * No language *listing* tool lives here: ps_mcp_tools already exposes
 * `get_languages`, so the install/toggle tools rely on that as their read
 * companion rather than duplicating it.
 *
 * The install_* tools reach prestashop.com to download packs (open world) and
 * are flagged destructive so the assistant routes them through operator
 * approval before they run.
 */
class LocalizationTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'install_language_pack',
        description: 'Download and install a language pack from prestashop.com by ISO code (e.g. "de", "pt", "nl"). Adds the language and its translations to the shop.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: true,
            idempotentHint: true,
            openWorldHint: true
        ),
        meta: ['category' => 'localization']
    )]
    #[PsMcpSchema(
        properties: [
            'iso' => ['type' => 'string', 'description' => 'Two-letter language ISO code to install (e.g. "de", "pt", "nl")'],
        ],
        required: ['iso']
    )]
    public function installLanguagePack(string $iso): array
    {
        try {
            $iso = strtolower(trim($iso));
            if (!\Validate::isLanguageIsoCode($iso)) {
                throw new \InvalidArgumentException(sprintf('Invalid language ISO code "%s".', $iso));
            }

            if (\Language::getIdByIso($iso)) {
                return ['content' => [
                    'iso_code' => $iso,
                    'installed' => true,
                    'already_installed' => true,
                    'id_lang' => (int) \Language::getIdByIso($iso),
                ]];
            }

            $result = \Language::downloadAndInstallLanguagePack($iso);
            if ($result !== true) {
                $message = is_array($result) ? implode('; ', $result) : (string) $result;

                throw new \RuntimeException(sprintf('Language pack "%s" failed to install: %s', $iso, $message));
            }

            return ['content' => [
                'iso_code' => $iso,
                'installed' => true,
                'already_installed' => false,
                'id_lang' => (int) \Language::getIdByIso($iso),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'install_localization_pack',
        description: 'Download and apply a country localisation pack by country ISO code (e.g. "DE", "ES"). Imports the selected country-specific data: taxes, currencies, states, units and/or groups.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: true,
            idempotentHint: false,
            openWorldHint: true
        ),
        meta: ['category' => 'localization']
    )]
    #[PsMcpSchema(
        properties: [
            'countryIso' => ['type' => 'string', 'description' => 'Two-letter country ISO code (e.g. "DE", "ES", "IT")'],
            'selection' => [
                'type' => 'array',
                'items' => ['type' => 'string', 'enum' => ['states', 'taxes', 'currencies', 'languages', 'units', 'groups']],
                'description' => 'Which parts of the pack to import (default: ["taxes","currencies","units"])',
            ],
        ],
        required: ['countryIso']
    )]
    public function installLocalizationPack(string $countryIso, ?array $selection = null): array
    {
        try {
            $countryIso = strtoupper(trim($countryIso));
            if (!\Validate::isLanguageIsoCode($countryIso)) {
                throw new \InvalidArgumentException(sprintf('Invalid country ISO code "%s".', $countryIso));
            }

            $allowed = ['states', 'taxes', 'currencies', 'languages', 'units', 'groups'];
            $selection = $selection !== null && $selection !== []
                ? array_values(array_intersect($allowed, $selection))
                : ['taxes', 'currencies', 'units'];

            $xml = $this->downloadLocalizationPack($countryIso);

            $localizationPack = new \LocalizationPack();
            if (!$localizationPack->loadLocalisationPack($xml, $selection, true, $countryIso)) {
                throw new \RuntimeException(sprintf('Localisation pack "%s" could not be applied (the pack may be unavailable for this country or PrestaShop version).', $countryIso));
            }

            return ['content' => [
                'country_iso' => $countryIso,
                'applied' => true,
                'selection' => $selection,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'set_default_language',
        description: 'Set an already-installed language (by ISO code) as the shop default.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'localization']
    )]
    #[PsMcpSchema(
        properties: [
            'iso' => ['type' => 'string', 'description' => 'ISO code of an installed language to make the default (e.g. "fr")'],
        ],
        required: ['iso']
    )]
    public function setDefaultLanguage(string $iso): array
    {
        try {
            $iso = strtolower(trim($iso));
            $idLang = (int) \Language::getIdByIso($iso);
            if ($idLang <= 0) {
                throw new \RuntimeException(sprintf('Language "%s" is not installed. Install it first with install_language_pack.', $iso));
            }

            \Configuration::updateValue('PS_LANG_DEFAULT', $idLang);

            return ['content' => ['iso_code' => $iso, 'id_lang' => $idLang, 'is_default' => true]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'toggle_language',
        description: 'Activate or deactivate an installed language by ISO code.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'localization']
    )]
    #[PsMcpSchema(
        properties: [
            'iso' => ['type' => 'string', 'description' => 'ISO code of the installed language (e.g. "es")'],
            'active' => ['type' => 'boolean', 'description' => 'true to activate, false to deactivate'],
        ],
        required: ['iso', 'active']
    )]
    public function toggleLanguage(string $iso, bool $active): array
    {
        try {
            $iso = strtolower(trim($iso));
            $idLang = (int) \Language::getIdByIso($iso);
            if ($idLang <= 0) {
                throw new \RuntimeException(sprintf('Language "%s" is not installed.', $iso));
            }

            $language = new \Language($idLang);
            $language->active = $active;
            if (!$language->save()) {
                throw new \RuntimeException(sprintf('Failed to update language "%s".', $iso));
            }

            return ['content' => ['iso_code' => $iso, 'id_lang' => $idLang, 'active' => $active]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Fetch the localisation pack XML for a country from the PrestaShop API.
     *
     * NOTE: the exact endpoint is owned by core (AdminLocalizationController uses
     * the same _PS_API_URL_ host). Verify against the running core if a future
     * PrestaShop release changes it.
     */
    private function downloadLocalizationPack(string $countryIso): string
    {
        $host = defined('_PS_API_URL_') ? rtrim((string) constant('_PS_API_URL_'), '/') : 'http://api.prestashop.com';
        $url = $host . '/localization/' . _PS_VERSION_ . '/' . $countryIso . '.xml';

        $xml = \Tools::file_get_contents($url);
        if (!is_string($xml) || trim($xml) === '') {
            throw new \RuntimeException(sprintf('Could not download localisation pack for "%s" from %s.', $countryIso, $url));
        }

        return $xml;
    }
}
