{*
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
*}

{if (isset($mbe_uap) && $mbe_uap)}
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    if (typeof jQuery === 'undefined') {
        document.write(unescape("%3Cscript src='js/jquery.js' type='text/javascript'%3E%3C/script%3E"));
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var uapList{$carrierId|escape:'javascript':'UTF-8'} = '{$suap|escape:'javascript':'UTF-8'}';
        var setUAP{$carrierId|escape:'javascript':'UTF-8'} = '{$link->getPageLink('mbeuap&fc=module&module=mbeshipping&ajax=1&action=SetUAP')|escape:'javascript':'UTF-8'}';

        $( "#mbeuap{$carrierId|escape:'javascript':'UTF-8'}" ).change(function () {
            var selectedUap = $("#mbeuap{$carrierId|escape:'javascript':'UTF-8'}").children("option:selected").val();
            $.ajax({
                type: 'POST',
                url: setUAP{$carrierId|escape:'javascript':'UTF-8'},
                data: {
                    ajax : true,
                    mbeSelectedUap: selectedUap,
                    mbeUapList: uapList{$carrierId|escape:'javascript':'UTF-8'},
                },
                success: function(resp, textStatus, jqXHR)
                {
                }
            });
        });
    });
</script>

<div class="form-group row" style="background: #ffffff; border: 1px solid #d6d4d4; padding: 14px 18px 13px; margin: 0 0 30px 0; line-height: 23px;">
    <div class="col-md-12">
        <p style="font-weight: bold">{l s='Set Shipping address to UAP' mod='mbeshipping'}<br/>
        </p>
        <select class="form-control" id="mbeuap{$carrierId|escape:'html':'UTF-8'}" required="required">
            <option value="">{l s='Select a UAP' mod='mbeshipping'}</option>
            {foreach $uapList as $uap}
                <option value="{$uap['PublicAccessPointID']|escape:'html':'UTF-8'}">{$uap['Distance']|escape:'html':'UTF-8'} - {$uap['ConsigneeName']|escape:'html':'UTF-8'}, {$uap['AddressLine']|escape:'html':'UTF-8'}, {$uap['PoliticalDivision2']|escape:'html':'UTF-8'} ({$uap['StandardHoursOfOperation']|escape:'html':'UTF-8'})</option>
            {/foreach}
        </select>
    </div>
</div>
<div></div>
<div class="form-group row"></div>
{/if}
