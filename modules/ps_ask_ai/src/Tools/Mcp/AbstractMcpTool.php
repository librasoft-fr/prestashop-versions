<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * This file is part of the PrestaShop Enterprise solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Tools\Mcp;

/**
 * Shared helpers for the module's MCP tool classes.
 *
 * Tools access the shop through PrestaShop core classes directly (Category,
 * Manufacturer, CartRule, ...) rather than the webservice layer, so this base
 * centralises the small cross-cutting concerns: language/shop resolution, limit
 * clamping, running a DbQuery into an array and expanding a scalar into the
 * per-language shape ObjectModels expect. Keeping that logic here lets each
 * tool method stay short and single-purpose.
 *
 * This class declares no #[PsMcpTool] methods, so ps_mcp_server's discoverer
 * skips it; only concrete subclasses expose tools.
 */
abstract class AbstractMcpTool
{
    /** Hard ceiling on rows returned by any list/search tool. */
    protected const MAX_LIMIT = 100;

    /** Default page size when the caller omits `limit`. */
    protected const DEFAULT_LIMIT = 10;

    /**
     * Resolve a usable language id: the caller value, else the current BO
     * context language, else the shop default.
     */
    protected function resolveLangId(?int $langId): int
    {
        if ($langId !== null && $langId > 0) {
            return $langId;
        }

        $context = \Context::getContext();
        if ($context !== null && $context->language !== null && (int) $context->language->id > 0) {
            return (int) $context->language->id;
        }

        return (int) \Configuration::get('PS_LANG_DEFAULT');
    }

    /**
     * Resolve the shop id to operate on (current context shop, else default).
     */
    protected function resolveShopId(?int $shopId): int
    {
        if ($shopId !== null && $shopId > 0) {
            return $shopId;
        }

        $context = \Context::getContext();
        if ($context !== null && $context->shop !== null && (int) $context->shop->id > 0) {
            return (int) $context->shop->id;
        }

        return (int) \Configuration::get('PS_SHOP_DEFAULT') ?: 1;
    }

    /**
     * Clamp a requested limit into a sane [1, MAX_LIMIT] range.
     */
    protected function clampLimit(int $limit): int
    {
        if ($limit <= 0) {
            return static::DEFAULT_LIMIT;
        }

        return min($limit, static::MAX_LIMIT);
    }

    /**
     * Execute a DbQuery and always return an array (never false/null), so
     * callers can map over the result without extra guards.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function rows(\DbQuery $query): array
    {
        $result = \Db::getInstance()->executeS($query);

        return is_array($result) ? $result : [];
    }

    /**
     * Expand a single scalar into the per-language array shape PrestaShop
     * ObjectModels expect for lang fields (`[idLang => value, ...]`).
     *
     * @return array<int, string>
     */
    protected function multilangValue(string $value): array
    {
        $byLang = [];
        foreach (\Language::getLanguages(false) as $language) {
            $byLang[(int) $language['id_lang']] = $value;
        }

        return $byLang;
    }

    /** Default look-back window, in days, for analytics tools with no date range. */
    protected const DEFAULT_WINDOW_DAYS = 30;

    /**
     * Resolve and validate a [from, to] date window, defaulting to the last
     * DEFAULT_WINDOW_DAYS. Returned strings are validated PrestaShop datetimes,
     * safe to interpolate into a query.
     *
     * @return array{0: string, 1: string}
     */
    protected function resolveDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $from = $this->normaliseDate($dateFrom, date('Y-m-d 00:00:00', (int) strtotime('-' . static::DEFAULT_WINDOW_DAYS . ' days')), false);
        $to = $this->normaliseDate($dateTo, date('Y-m-d 23:59:59'), true);

        return [$from, $to];
    }

    /**
     * Validate a caller-supplied date. A bare "Y-m-d" is expanded to the start
     * or end of that day; anything invalid falls back to $default.
     */
    protected function normaliseDate(?string $value, string $default, bool $endOfDay): string
    {
        $value = $value !== null ? trim($value) : '';
        if ($value === '') {
            return $default;
        }
        if (!\Validate::isDate($value)) {
            return $default;
        }

        return $this->widenDateOnly($value, $endOfDay);
    }

    /**
     * Expand an already-validated date-only value to a full datetime. Left
     * untouched if it already carries a time.
     *
     * Validate::isDate() accepts both shapes, so callers that keep a date-only
     * value would send MySQL a midnight bound — an inclusive "to" bound then
     * silently drops that whole last day.
     */
    protected function widenDateOnly(string $value, bool $endOfDay): string
    {
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value) === 1) {
            return $value . ($endOfDay ? ' 23:59:59' : ' 00:00:00');
        }

        return $value;
    }

    /** ISO code of the shop default currency (amounts are normalised to it). */
    protected function defaultCurrencyIso(): string
    {
        $currency = new \Currency((int) \Configuration::get('PS_CURRENCY_DEFAULT'));

        return (string) $currency->iso_code;
    }
}
