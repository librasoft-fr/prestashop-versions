(()=>{"use strict";var e={r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},t={};e.r(t);
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
const{$:o}=window,i=0,l=1,s=2,r=3,n=4,a=5,d=6,p=7,h=8;class c{constructor(){o(".js-entity-select").on("change",(()=>this.toggleForm())),this.toggleForm()}toggleForm(){const e=o("#entity").find("option:selected"),t=parseInt(e.val(),10),i=e.text().toLowerCase();this.toggleEntityAlert(t),this.toggleFields(t,i),this.loadAvailableFields(t)}toggleEntityAlert(e){const t=o(".js-entity-alert");[i,l].includes(e)?t.show():t.hide()}toggleFields(e,t){const c=o(".js-truncate-form-group"),m=o(".js-match-ref-form-group"),f=o(".js-regenerate-form-group"),F=o(".js-force-ids-form-group"),u=o(".js-entity-name");h===e?(c.hide(),c.find('input[name="truncate"]').first().trigger("click")):c.show(),[l,s].includes(e)?m.show():m.hide(),[i,l,a,d,h].includes(e)?f.show():f.hide(),[i,l,r,n,a,d,h,p].includes(e)?F.show():F.hide(),u.html(t)}loadAvailableFields(e){const t=o(".js-available-fields");o.ajax({url:t.data("url"),data:{entity:e},dataType:"json"}).then((e=>{this.removeAvailableFields(t);for(let o=0;o<e.length;o+=1)this.appendAvailableField(t,e[o].label+(e[o].required?"*":""),e[o].description);t.find('[data-toggle="popover"]').popover()}))}removeAvailableFields(e){e.find('[data-toggle="popover"]').popover("hide"),e.empty()}appendHelpBox(e,t){const i=o(".js-available-field-popover-template").clone();i.attr("data-content",t),i.removeClass("js-available-field-popover-template d-none"),e.append(i)}appendAvailableField(e,t,i){const l=o(".js-available-field-template").clone();l.text(t),i&&this.appendHelpBox(l,i),l.removeClass("js-available-field-template d-none"),l.appendTo(e)}}
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
const{$:m}=window;class f{constructor(){new c,m(".js-from-files-history-btn").on("click",(()=>this.showFilesHistoryHandler())),m(".js-close-files-history-block-btn").on("click",(()=>this.closeFilesHistoryHandler())),m("#fileHistoryTable").on("click",".js-use-file-btn",(e=>this.useFileFromFilesHistory(e))),m(".js-change-import-file-btn").on("click",(()=>this.changeImportFileHandler())),m(".js-import-file").on("change",(()=>this.uploadFile())),this.toggleSelectedFile(),this.handleSubmit()}handleSubmit(){m(".js-import-form").on("submit",(function(){const e=m(this);return"1"!==e.find('input[name="truncate"]:checked').val()||window.confirm(`${e.data("delete-confirm-message")} ${m.trim(m("#entity > option:selected").text().toLowerCase())}?`)}))}toggleSelectedFile(){const e=m("#csv").val();e&&e.length>0&&(this.showImportFileAlert(e),this.hideFileUploadBlock())}changeImportFileHandler(){this.hideImportFileAlert(),this.showFileUploadBlock()}showFilesHistoryHandler(){this.showFilesHistory(),this.hideFileUploadBlock()}closeFilesHistoryHandler(){this.closeFilesHistory(),this.showFileUploadBlock()}showFilesHistory(){m(".js-files-history-block").removeClass("d-none")}closeFilesHistory(){m(".js-files-history-block").addClass("d-none")}useFileFromFilesHistory(e){const t=m(e.target).closest(".btn-group").data("file");m(".js-import-file-input").val(t),this.showImportFileAlert(t),this.closeFilesHistory()}showImportFileAlert(e){m(".js-import-file-alert").removeClass("d-none"),m(".js-import-file").text(e)}hideImportFileAlert(){m(".js-import-file-alert").addClass("d-none")}hideFileUploadBlock(){m(".js-file-upload-form-group").addClass("d-none")}showFileUploadBlock(){m(".js-file-upload-form-group").removeClass("d-none")}enableFilesHistoryBtn(){m(".js-from-files-history-btn").removeAttr("disabled")}showImportFileError(e,t,o){const i=m(".js-import-file-error"),l=`${e} (${this.humanizeSize(t)})`;i.find(".js-file-data").text(l),i.find(".js-error-message").text(o),i.removeClass("d-none")}hideImportFileError(){m(".js-import-file-error").addClass("d-none")}humanizeSize(e){return"number"!=typeof e?"":e>=1e9?`${(e/1e9).toFixed(2)} GB`:e>=1e6?`${(e/1e6).toFixed(2)} MB`:`${(e/1e3).toFixed(2)} KB`}uploadFile(){this.hideImportFileError();const e=m("#file"),t=e.prop("files")[0];if(e.data("max-file-upload-size")<t.size)return void this.showImportFileError(t.name,t.size,"File is too large");const o=new FormData;o.append("file",t),m.ajax({type:"POST",url:m(".js-import-form").data("file-upload-url"),data:o,cache:!1,contentType:!1,processData:!1}).then((e=>{if(e.error)return void this.showImportFileError(t.name,t.size,e.error);const o=e.file.name;m(".js-import-file-input").val(o),this.showImportFileAlert(o),this.hideFileUploadBlock(),this.addFileToHistoryTable(o),this.enableFilesHistoryBtn()}))}addFileToHistoryTable(e){const t=m("#fileHistoryTable"),o=`${t.data("delete-file-url")}&filename=${encodeURIComponent(e)}`,i=`${t.data("download-file-url")}&filename=${encodeURIComponent(e)}`,l=t.find("tr:first").clone();l.removeClass("d-none"),l.find("td:first").text(e),l.find(".btn-group").attr("data-file",e),l.find(".js-delete-file-btn").attr("href",o),l.find(".js-download-file-btn").attr("href",i),t.find("tbody").append(l);const s=t.find("tr").length-1;m(".js-files-history-number").text(s)}}
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
const{$:F}=window;F((()=>{new f})),window.imports=t})();