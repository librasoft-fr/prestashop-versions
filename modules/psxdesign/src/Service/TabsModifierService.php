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

namespace PrestaShop\Module\PsxDesign\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Db;
use Language;
use PrestaShop\Module\PsxDesign\Install\Tabs\Tabs;
use PrestaShopBundle\Translation\TranslatorInterface as Translator;
use Tab;

class TabsModifierService
{
    /**
     * @var Translator
     */
    public $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function modifyTabs(array $params): void
    {
        $adminParentThemesTabId = (int) Tab::getInstanceFromClassName('AdminParentThemes')->id;
        $adminThemesTab = Tab::getInstanceFromClassName('AdminThemes');

        //In some cases other modules duplicating AdminThemes and that tab is disabled so we want to turn on AdminThemes parent tab
        //We do that then AdminThemes parent is not default one

        if ($params['object']->class_name === 'AdminThemes' && $adminParentThemesTabId !== (int) $adminThemesTab->id_parent) {
            $adminThemesParent = new Tab((int) $adminThemesTab->id_parent);
            $adminThemesParent->active = true;

            $names = Tabs::getThemeModuleTabName();

            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $adminThemesParent->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            //and we change position so tab will be below ours
            $adminThemesParent->position = 1;
            $adminThemesParent->update();
        }

        // If AdminThemes parent tab is default one we want to keep it hidden as we know our module is installed
        if ($params['object']->class_name === 'AdminThemes' && $adminParentThemesTabId === (int) $adminThemesTab->id_parent) {
            Db::getInstance()->update('tab', ['active' => 0], 'id_tab = ' . (int) $adminThemesTab->id);
        }
    }
}
