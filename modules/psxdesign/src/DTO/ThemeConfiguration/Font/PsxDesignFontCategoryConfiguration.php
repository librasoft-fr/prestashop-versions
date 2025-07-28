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

namespace PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\HttpFoundation\ParameterBag;

class PsxDesignFontCategoryConfiguration
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $key;

    private function __construct(ParameterBag $data)
    {
        $this->setTitle($data->get('title') ?? '');
        $this->setDescription($data->get('description') ?? '');
        $this->setKey($data->get('key') ?? '');
    }

    /**
     * @param array $fontCategoryConfiguration
     *
     * @return PsxDesignFontCategoryConfiguration
     */
    public static function createFromThemeConfiguration(array $fontCategoryConfiguration): self
    {
        $parameterBag = new ParameterBag($fontCategoryConfiguration);

        return new self($parameterBag);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    private function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    /**
     * @param string $description
     *
     * @return void
     */
    private function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    private function setKey(string $key): void
    {
        $this->key = trim($key);
    }
}
