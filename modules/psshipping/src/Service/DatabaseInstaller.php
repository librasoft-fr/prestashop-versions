<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Service;

use Db;
use Exception;
use Module;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;

/**
 * Service responsible for database operations during module installation/uninstallation
 */
class DatabaseInstaller
{
    /** @var Module */
    private $module;

    /** @var string */
    private const SQL_DIRECTORY = 'ressources/sql/';

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Execute install SQL operations
     *
     * @throws PsshippingException
     */
    public function install(): bool
    {
        return $this->executeSqlFile('install.sql');
    }

    /**
     * Execute uninstall SQL operations
     *
     * @throws PsshippingException
     */
    public function uninstall(): bool
    {
        return $this->executeSqlFile('uninstall.sql');
    }

    /**
     * Execute SQL queries from a file
     *
     * @throws PsshippingException
     */
    private function executeSqlFile(string $filename): bool
    {
        $sqlFilePath = $this->module->getLocalPath() . self::SQL_DIRECTORY . $filename;

        if (!file_exists($sqlFilePath)) {
            throw new PsshippingException(sprintf('The file %s does not exist in %u directory.', $filename, self::SQL_DIRECTORY));
        }

        $sqlContent = file_get_contents($sqlFilePath);
        if ($sqlContent === false) {
            throw new PsshippingException(sprintf('Cannot retrieve the content of the file %s', $filename));
        }

        return $this->executeSqlContent($sqlContent);
    }

    /**
     * Execute SQL content
     */
    private function executeSqlContent(string $sqlContent): bool
    {
        $sqlContent = str_replace('_DB_PREFIX_', _DB_PREFIX_, $sqlContent);
        $sqlContent = $this->cleanSqlContent($sqlContent);

        $queries = array_filter(array_map('trim', explode(';', $sqlContent)));

        if (empty($queries)) {
            return true;
        }

        foreach ($queries as $query) {
            try {
                Db::getInstance()->execute($query);
            } catch (Exception $e) {
                throw new PsshippingException(sprintf('Error when trying to execute the sql query: %s', $e->getMessage()));
            }
        }

        return true;
    }

    /**
     * Clean SQL content by removing comments and normalizing whitespace
     */
    private function cleanSqlContent(string $sqlContent): string
    {
        // remove comments
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent) ?? $sqlContent;
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent) ?? $sqlContent;

        $sqlContent = preg_replace('/\s+/', ' ', $sqlContent) ?? $sqlContent;

        return trim($sqlContent);
    }
}
