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

namespace PrestaShop\Module\PsxDesign\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionAdminControllerSetMedia;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionDispatcherBefore;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionFrontControllerSetMedia;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionObjectLanguageAddAfter;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionObjectTabUpdateAfter;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseActionThemeConfiguration;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseDisplayAfterTitleTag;
use PrestaShop\Module\PsxDesign\Traits\Hooks\UseDisplayBackOfficeHeader;
use Symfony\Component\String\UnicodeString;

trait UseHooks
{
    use UseDisplayBackOfficeHeader;
    use UseActionThemeConfiguration;
    use UseActionFrontControllerSetMedia;
    use UseActionAdminControllerSetMedia;
    use UseDisplayAfterTitleTag;
    use UseActionObjectTabUpdateAfter;
    use UseActionDispatcherBefore;
    use UseActionObjectLanguageAddAfter;

    /**
     * Try to call the "hookClassNameExtraOperations" method on each hook class.
     *
     * @return void
     */
    protected function installHooks(): void
    {
        foreach ($this->getTraitNames() as $traitName) {
            $traitName = lcfirst($traitName);
            if (method_exists($this, "{$traitName}ExtraOperations")) {
                $this->{"{$traitName}ExtraOperations"}();
            }
        }
    }

    /**
     * Guess the hooks names by using the traits names. Remove the "Use" in the traits name.
     *
     * @return string[]
     */
    protected function getHooksNames(): array
    {
        return array_map(function ($trait) {
            return str_replace('Use', '', $trait);
        }, $this->getTraitNames());
    }

    /**
     * Parse all classes used by this trait, and extract them
     *
     * @return string[]
     */
    protected function getTraitNames(): array
    {
        $traits = [];
        // Retrieve all used classes and iterate
        foreach (class_uses(UseHooks::class) as $trait) {
            // Get only the class name e.g. 'UseAdminControllerSetMedia'
            $traits[] = (new UnicodeString($trait))->afterLast('\\')->toString();
        }

        return $traits;
    }
}
