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
const{$:e}=window,t=0,i=1,o=2,l=3,s=4,r=5,a=6,n=7,d=8;class p{constructor(){e(".js-entity-select").on("change",(()=>this.toggleForm())),this.toggleForm()}toggleForm(){const t=e("#entity").find("option:selected"),i=parseInt(t.val(),10),o=t.text().toLowerCase();this.toggleEntityAlert(i),this.toggleFields(i,o),this.loadAvailableFields(i)}toggleEntityAlert(o){const l=e(".js-entity-alert");[t,i].includes(o)?l.show():l.hide()}toggleFields(p,h){const c=e(".js-truncate-form-group"),m=e(".js-match-ref-form-group"),f=e(".js-regenerate-form-group"),F=e(".js-force-ids-form-group"),u=e(".js-entity-name");d===p?(c.hide(),c.find('input[name="truncate"]').first().trigger("click")):c.show(),[i,o].includes(p)?m.show():m.hide(),[t,i,r,a,d].includes(p)?f.show():f.hide(),[t,i,l,s,r,a,d,n].includes(p)?F.show():F.hide(),u.html(h)}loadAvailableFields(t){const i=e(".js-available-fields");e.ajax({url:i.data("url"),data:{entity:t},dataType:"json"}).then((e=>{this.removeAvailableFields(i);for(let t=0;t<e.length;t+=1)this.appendAvailableField(i,e[t].label+(e[t].required?"*":""),e[t].description);i.find('[data-toggle="popover"]').popover()}))}removeAvailableFields(e){e.find('[data-toggle="popover"]').popover("hide"),e.empty()}appendHelpBox(t,i){const o=e(".js-available-field-popover-template").clone();o.attr("data-content",i),o.removeClass("js-available-field-popover-template d-none"),t.append(o)}appendAvailableField(t,i,o){const l=e(".js-available-field-template").clone();l.text(i),o&&this.appendHelpBox(l,o),l.removeClass("js-available-field-template d-none"),l.appendTo(t)}}
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
const{$:h}=window;class c{constructor(){new p,h(".js-from-files-history-btn").on("click",(()=>this.showFilesHistoryHandler())),h(".js-close-files-history-block-btn").on("click",(()=>this.closeFilesHistoryHandler())),h("#fileHistoryTable").on("click",".js-use-file-btn",(e=>this.useFileFromFilesHistory(e))),h(".js-change-import-file-btn").on("click",(()=>this.changeImportFileHandler())),h(".js-import-file").on("change",(()=>this.uploadFile())),this.toggleSelectedFile(),this.handleSubmit()}handleSubmit(){h(".js-import-form").on("submit",(function(){const e=h(this);return"1"!==e.find('input[name="truncate"]:checked').val()||window.confirm(`${e.data("delete-confirm-message")} ${h.trim(h("#entity > option:selected").text().toLowerCase())}?`)}))}toggleSelectedFile(){const e=h("#csv").val();e&&e.length>0&&(this.showImportFileAlert(e),this.hideFileUploadBlock())}changeImportFileHandler(){this.hideImportFileAlert(),this.showFileUploadBlock()}showFilesHistoryHandler(){this.showFilesHistory(),this.hideFileUploadBlock()}closeFilesHistoryHandler(){this.closeFilesHistory(),this.showFileUploadBlock()}showFilesHistory(){h(".js-files-history-block").removeClass("d-none")}closeFilesHistory(){h(".js-files-history-block").addClass("d-none")}useFileFromFilesHistory(e){const t=h(e.target).closest(".btn-group").data("file");h(".js-import-file-input").val(t),this.showImportFileAlert(t),this.closeFilesHistory()}showImportFileAlert(e){h(".js-import-file-alert").removeClass("d-none"),h(".js-import-file").text(e)}hideImportFileAlert(){h(".js-import-file-alert").addClass("d-none")}hideFileUploadBlock(){h(".js-file-upload-form-group").addClass("d-none")}showFileUploadBlock(){h(".js-file-upload-form-group").removeClass("d-none")}enableFilesHistoryBtn(){h(".js-from-files-history-btn").removeAttr("disabled")}showImportFileError(e,t,i){const o=h(".js-import-file-error"),l=`${e} (${this.humanizeSize(t)})`;o.find(".js-file-data").text(l),o.find(".js-error-message").text(i),o.removeClass("d-none")}hideImportFileError(){h(".js-import-file-error").addClass("d-none")}humanizeSize(e){return"number"!=typeof e?"":e>=1e9?`${(e/1e9).toFixed(2)} GB`:e>=1e6?`${(e/1e6).toFixed(2)} MB`:`${(e/1e3).toFixed(2)} KB`}uploadFile(){this.hideImportFileError();const e=h("#file"),t=e.prop("files")[0];if(e.data("max-file-upload-size")<t.size)return void this.showImportFileError(t.name,t.size,"File is too large");const i=new FormData;i.append("file",t),h.ajax({type:"POST",url:h(".js-import-form").data("file-upload-url"),data:i,cache:!1,contentType:!1,processData:!1}).then((e=>{if(e.error)return void this.showImportFileError(t.name,t.size,e.error);const i=e.file.name;h(".js-import-file-input").val(i),this.showImportFileAlert(i),this.hideFileUploadBlock(),this.addFileToHistoryTable(i),this.enableFilesHistoryBtn()}))}addFileToHistoryTable(e){const t=h("#fileHistoryTable"),i=`${t.data("delete-file-url")}&filename=${encodeURIComponent(e)}`,o=`${t.data("download-file-url")}&filename=${encodeURIComponent(e)}`,l=t.find("tr:first").clone();l.removeClass("d-none"),l.find("td:first").text(e),l.find(".btn-group").attr("data-file",e),l.find(".js-delete-file-btn").attr("href",i),l.find(".js-download-file-btn").attr("href",o),t.find("tbody").append(l);const s=t.find("tr").length-1;h(".js-files-history-number").text(s)}}
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
const{$:m}=window;m((()=>{new c})),window.imports={}})();