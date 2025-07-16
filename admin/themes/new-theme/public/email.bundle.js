(()=>{"use strict";
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
const{$:e}=window;const s=class{constructor(){this.$successAlert=e(".js-test-email-success"),this.$errorAlert=e(".js-test-email-errors"),this.$loader=e(".js-test-email-loader"),this.$sendEmailBtn=e(".js-send-test-email-btn"),this.$sendEmailBtn.on("click",(e=>{this.handle(e)}))}handle(s){e("#test_email_sending_mail_method").val(e('input[name="form[mail_method]"]:checked').val()),e("#test_email_sending_smtp_server").val(e("#form_smtp_config_server").val()),e("#test_email_sending_smtp_username").val(e("#form_smtp_config_username").val()),e("#test_email_sending_smtp_password").val(e("#form_smtp_config_password").val()),e("#test_email_sending_smtp_port").val(e("#form_smtp_config_port").val()),e("#test_email_sending_smtp_encryption").val(e("#form_smtp_config_encryption").val()),e("#test_email_sending_dkim_enable").val(e('input[name="form[dkim_enable]"]:checked').val()),e("#test_email_sending_dkim_key").val(e("#form_dkim_config_key").val()),e("#test_email_sending_dkim_selector").val(e("#form_dkim_config_selector").val()),e("#test_email_sending_dkim_domain").val(e("#form_dkim_config_domain").val());const n=e(s.currentTarget).closest("form");this.resetMessages(),this.hideSendEmailButton(),this.showLoader(),e.post({url:n.attr("action"),data:n.serialize()}).then((e=>{this.hideLoader(),this.showSendEmailButton(),0===e.errors.length?this.showSuccess():this.showErrors(e.errors)}))}resetMessages(){this.hideSuccess(),this.hideErrors()}showSuccess(){this.$successAlert.removeClass("d-none")}hideSuccess(){this.$successAlert.addClass("d-none")}showLoader(){this.$loader.removeClass("d-none")}hideLoader(){this.$loader.addClass("d-none")}showErrors(e){e.forEach((e=>{this.$errorAlert.append(`<p>${e}</p>`)})),this.$errorAlert.removeClass("d-none")}hideErrors(){this.$errorAlert.addClass("d-none").empty()}showSendEmailButton(){this.$sendEmailBtn.removeClass("d-none")}hideSendEmailButton(){this.$sendEmailBtn.addClass("d-none")}},{$:n}=window;const t=class{constructor(){n(".js-email-method").on("change",'input[type="radio"]',(e=>{const s=Number(n(e.currentTarget).val());n(".js-smtp-configuration").toggleClass("d-none",this.getSmtpMailMethodOption()!==s)}))}getSmtpMailMethodOption(){return n(".js-email-method").data("smtp-mail-method")}},o=".js-dkim-enable",i=".js-dkim-configuration",{$:a}=window;const r=class{constructor(){a(o).on("change",(e=>{const s=Number(a(e.currentTarget).val());a(i).toggleClass("d-none",0===s)}))}},{$:d}=window;d((()=>{const e=new window.prestashop.component.Grid("email_logs");e.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),e.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),e.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),e.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),e.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),new s,new t,new r})),window.email={}})();