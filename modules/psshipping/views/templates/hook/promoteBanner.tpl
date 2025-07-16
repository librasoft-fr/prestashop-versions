{**
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
 *}

<script src="{$appLink|escape:'htmlall':'UTF-8'}" type="module"></script>
<link rel="stylesheet" href="{$appCss|escape:'htmlall':'UTF-8'}" type="text/css" media="all">

<script>
document.addEventListener('DOMContentLoaded', function () {
  const contentMessageBox = document.querySelector('#content-message-box');

  if (!contentMessageBox) {
      return;
  }

  let promoteBanner = document.createElement('div');
  promoteBanner.setAttribute('id', 'promotePsshippingBanner');
  const content = `
    <i18n-host>
      <promote-shipping></promote-shipping>
    </i18n-host>
  `;
  promoteBanner.innerHTML = content;

  contentMessageBox.appendChild(promoteBanner);
});
</script>
