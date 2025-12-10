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
<div class="gel-proximity-container">
    {include file="../dist/src/app/gelProximity/index.html"}

    <div id="gelProximityPickupError" hidden class="notifications-container container">
        <div class="alert alert-danger" role="alert" data-alert="danger">
            {$pickupSelectionError}
        </div>
    </div>

    {* Keep the following <div> on a single line to ensure it is truly empty, so the :empty selector works correctly *}
    <div id="gelProximityPickupDetails">{if isset($pickupPointDetail)}{$pickupPointDetail nofilter}{/if}</div>
</div>
