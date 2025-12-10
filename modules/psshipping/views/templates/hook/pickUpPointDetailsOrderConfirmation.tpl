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
{if isset($pickupPoint)}
    <div class="card border-1 mb-4">
        <div class="card-body">
            <h2>{$detailsTitle} {$pickupPoint.description}</h2>

            <address class="mb-0">
                {$pickupPoint.address}<br>
                {$pickupPoint.zipCode} {$pickupPoint.city}{if !empty($pickupPoint.department)} {$pickupPoint.department}{/if}<br>
                {$pickupPoint.country}
            </address>
        </div>
    </div>
{/if}
