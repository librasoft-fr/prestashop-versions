<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsxDesign\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

class DirectoryUtility
{
    /**
     * @param string $directoryPath
     *
     * @return void
     */
    public static function createDirIfNotExist(string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            // The folder does not exist, we will create it.
            if (!mkdir($directoryPath, 0777, true)) {
                throw new RuntimeException("Unable to create directory $directoryPath.");
            }
        }
    }

    /**
     * @param string $directoryToCopy
     * @param string $pathWhereCopied
     *
     * @return void
     */
    public static function copyDirectory(string $directoryToCopy, string $pathWhereCopied): void
    {
        if (!is_dir($directoryToCopy)) {
            throw new InvalidArgumentException("$directoryToCopy is not a valid directory.");
        }

        $contents = scandir($directoryToCopy);
        if ($contents === false) {
            throw new RuntimeException("Unable to read the contents of $directoryToCopy.");
        }

        // Make the destination directory if it does not exist
        self::createDirIfNotExist($pathWhereCopied);

        foreach ($contents as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourceFilePath = $directoryToCopy . DIRECTORY_SEPARATOR . $file;
                $destinationFilePath = $pathWhereCopied . DIRECTORY_SEPARATOR . $file;

                if (is_dir($sourceFilePath)) {
                    // Recursively copy subdirectories
                    self::copyDirectory($sourceFilePath, $destinationFilePath);
                } else {
                    if (!copy($sourceFilePath, $destinationFilePath)) {
                        throw new RuntimeException("Unable to copy file from $sourceFilePath to $destinationFilePath.");
                    }
                }
            }
        }
    }

    /**
     * @param string $sourceFile
     * @param string $destinationFilePath
     *
     * @return void
     */
    public static function copyFile(string $sourceFile, string $destinationFilePath): void
    {
        $destinationDirectory = dirname($destinationFilePath); // Obtient le répertoire parent du fichier de destination

        if (!is_dir($destinationDirectory)) {
            // Crée le répertoire de destination s'il n'existe pas
            mkdir($destinationDirectory, 0777, true); // Le troisième paramètre crée les répertoires parent si nécessaire.
        }

        copy($sourceFile, $destinationFilePath);
    }

    /**
     * @param string $directoryToDelete
     *
     * @return void
     */
    public static function deleteDirectory(string $directoryToDelete): void
    {
        if (!is_dir($directoryToDelete)) {
            throw new InvalidArgumentException("$directoryToDelete is not a valid directory.");
        }

        $contents = scandir($directoryToDelete);
        if ($contents === false) {
            throw new RuntimeException("Unable to read the contents of $directoryToDelete.");
        }

        foreach ($contents as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $directoryToDelete . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    self::deleteDirectory($filePath);
                } else {
                    if (!unlink($filePath)) {
                        throw new RuntimeException("Unable to delete the file $filePath.");
                    }
                }
            }
        }

        if (!rmdir($directoryToDelete)) {
            throw new RuntimeException("Unable to delete the directory $directoryToDelete.");
        }
    }
}
