<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Mcp;

/**
 * Splits the tool names advertised by ps_mcp_server back into their parts.
 *
 * The server's CustomDiscoverer prefixes every tool with the technical name
 * of the module that declares it, joined by a single '-':
 *
 *     {module_technical_name}-{tool_name}   e.g. ps_ask_ai-get_inventory_value
 *
 * The '-' separator is unambiguous by construction: PrestaShop module
 * technical names and PHP method names can never contain a dash, so the
 * first '-' always marks the boundary. A name without any '-' means the
 * discoverer could not resolve the owning module and skipped the prefix.
 */
final class McpToolName
{
    /** @var array<string, string> module technical name → display label, per request */
    private static array $labelCache = [];

    /** @var array<string, bool> module technical name → is-third-party, per request */
    private static array $externalCache = [];

    /**
     * Split a raw tool name and resolve the owning module's display label.
     * `name` always carries the raw input so existing consumers keep working.
     *
     * @return array{name: string, tool: string, module: ?string, moduleLabel: ?string, sourceExternal: bool}
     */
    public static function describe(string $rawName): array
    {
        [$module, $tool] = self::split($rawName);

        return [
            'name' => $rawName,
            'tool' => $tool,
            'module' => $module,
            'moduleLabel' => $module !== null ? self::moduleLabel($module) : null,
            'sourceExternal' => $module !== null && self::isExternal($module),
        ];
    }

    /**
     * @return array{0: ?string, 1: string} [module technical name or null, bare tool name]
     */
    public static function split(string $rawName): array
    {
        $pos = strpos($rawName, '-');
        // No dash → unprefixed name; dash at position 0 or last → malformed,
        // keep the raw name whole rather than fabricate an empty part.
        if ($pos === false || $pos === 0 || $pos === strlen($rawName) - 1) {
            return [null, $rawName];
        }

        return [substr($rawName, 0, $pos), substr($rawName, $pos + 1)];
    }

    /**
     * Human-readable label for a module technical name: the installed
     * module's displayName when resolvable (e.g. "PrestaShop AskAI"),
     * otherwise the humanized slug (e.g. "Ps ask ai").
     */
    public static function moduleLabel(string $moduleName): string
    {
        if (isset(self::$labelCache[$moduleName])) {
            return self::$labelCache[$moduleName];
        }

        $label = ucfirst(str_replace('_', ' ', $moduleName));
        if (class_exists(\Module::class)) {
            try {
                $module = \Module::getInstanceByName($moduleName);
                if ($module instanceof \Module && is_string($module->displayName) && $module->displayName !== '') {
                    $label = $module->displayName;
                }
            } catch (\Throwable) {
                // Unresolvable module (uninstalled, broken autoload): keep the slug.
            }
        }

        return self::$labelCache[$moduleName] = $label;
    }

    /**
     * Whether a module is third-party (not published by PrestaShop). Drives
     * the "External source" provenance line in the approval UI. Resolves the
     * installed module and compares its author against "PrestaShop".
     * An unresolvable module (uninstalled, broken autoload) or one with no
     * author is treated as first-party — we don't flag "external" when we
     * can't actually tell.
     */
    public static function isExternal(string $moduleName): bool
    {
        if (isset(self::$externalCache[$moduleName])) {
            return self::$externalCache[$moduleName];
        }

        $external = false;
        if (class_exists(\Module::class)) {
            try {
                $module = \Module::getInstanceByName($moduleName);
                if ($module instanceof \Module && is_string($module->author) && $module->author !== '') {
                    $external = strcasecmp(trim($module->author), 'PrestaShop') !== 0;
                }
            } catch (\Throwable) {
                // Unresolvable module: keep the safe first-party default.
            }
        }

        return self::$externalCache[$moduleName] = $external;
    }
}
