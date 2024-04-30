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

        if( $( "#delivery_option_{$carrierId|escape:'javascript':'UTF-8'}" ).is(':checked')){
            $( "#mbeuap{$carrierId|escape:'javascript':'UTF-8'}" ).prop('required', true);
        }

        $('input:radio').change(function() {
            if(this.id === "delivery_option_{$carrierId|escape:'javascript':'UTF-8'}")
            {
                $( "#mbeuap{$carrierId|escape:'javascript':'UTF-8'}" ).prop('required', true);//.attr('required', 'required');
            } else
            {
                $( "#mbeuap{$carrierId|escape:'javascript':'UTF-8'}").prop('required', false);//.removeAttr('required');
            }
        });
        var uapList = '{$suap|escape:'javascript':'UTF-8'}';
        $( "#mbeuap{$carrierId|escape:'javascript':'UTF-8'}" ).change(function () {
            var selectedUap = $("#mbeuap{$carrierId|escape:'javascript':'UTF-8'}").children("option:selected").val();

            var setUAP{$carrierId|escape:'javascript':'UTF-8'} ='{url entity='module' name='mbeshipping' controller='mbeuap' params=['ajax' => true, 'mbeSelectedUap' => selectedUap, 'mbeUapList' => uapList, 'action' => 'setUAP']}';

            $.ajax({
                type: 'POST',
                url: setUAP{$carrierId|escape:'javascript':'UTF-8'},
                cache: false,
                data: {
                    ajax : true,
                    action: 'setUAP',
                    mbeSelectedUap: selectedUap,
                    mbeUapList: uapList,
                },
                success: function(resp, textStatus, jqXHR)
                {
                    console.log('SET UAP');
                },
                error: function(res,textStatus,jqXHR)
                {
                    console.log(res);
                    console.log(textStatus);
                    console.log(jqXHR);
                }
            });
        });
    });
</script>

<div class="form-group row">
    <div class="col-md-12">
        <p>{l s='Set Shipping address to UAP' mod='mbeshipping'}
        </p>
        <select class="form-control" id="mbeuap{$carrierId|escape:'html':'UTF-8'}" >
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
