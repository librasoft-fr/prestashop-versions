var u=Object.defineProperty;var c=(s,e,t)=>e in s?u(s,e,{enumerable:!0,configurable:!0,writable:!0,value:t}):s[e]=t;var i=(s,e,t)=>(c(s,typeof e!="symbol"?e+"":e,t),t);import{k as f}from"./sentry-96f4e833.js";import{e as p}from"./dom-utils-d77254b8.js";/**
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
 */class g{constructor(){this.autoDiscover()}autoDiscover(){document.querySelectorAll(".file-uploader").forEach(e=>{new h({el:e})})}}class h{constructor(e){i(this,"el");i(this,"input");i(this,"form");i(this,"template");i(this,"previewContainer");i(this,"files");i(this,"deleteFile",e=>{if(this.files==null)return;const t=Array.from(this.files),r=t.findIndex(a=>a.name===e.name);t.splice(r,1),this.files=p(t),this.updateForm()});i(this,"dropEle",e=>{e.preventDefault(),this.el.classList.contains("file-uploader--drag-hover")&&this.el.classList.remove("file-uploader--drag-hover"),this.addFile(e)});i(this,"addFile",e=>{let t;if("dataTransfer"in e&&(t=e.dataTransfer!=null?e.dataTransfer.files:this.input.files),t!=null){if(!this.input.multiple)this.files=p([t[0]]);else if(this.files!=null){const r=Array.from(this.files);Array.from(t).forEach(l=>{const n=r.find(o=>o.name===l.name);n!=null&&n.type===l.type&&n.size===l.size||r.push(l)}),this.files=p(r)}this.updateForm()}});i(this,"updateForm",()=>{this.input.files=this.files,this.form.dispatchEvent(f)});i(this,"dragOver",e=>{e.preventDefault(),this.el.classList.contains("file-uploader--drag-hover")||this.el.classList.add("file-uploader--drag-hover")});i(this,"dragLeave",e=>{e.preventDefault(),this.el.classList.contains("file-uploader--drag-hover")&&this.el.classList.remove("file-uploader--drag-hover")});this.el=e.el,this.input=this.el.querySelector('input[type="file"]'),this.form=this.input.form,this.template=this.el.querySelector("template.file-uploader__template-previews"),this.previewContainer=this.el.querySelector(".file-uploader__preview-container"),this.files=p([]),this.init()}init(){if(this.el===null||this.input===null)return;this.el.ondrop=this.dropEle,this.el.ondragover=this.dragOver,this.el.ondragleave=this.dragLeave;const e=this.input,t=this.el.querySelector(".file-uploader__legend-btn");t!=null&&t.addEventListener("click",()=>{e.click()}),this.input.onchange=this.addFile,this.form.addEventListener("input",()=>{this.updatePreviews()})}updatePreviews(){this.previewContainer.innerHTML="",Array.from(this.input.files).forEach(e=>{const t=e,r=this.template.content.cloneNode(!0),a=t.type.includes("image")&&URL.createObjectURL(t),l=t.name,n=r.querySelector("p.file-uploader__name");if(n.title=l,n.textContent=l,a!==!1){const d=r.querySelector("img.file-uploader__preview-img");d.src=a,d.alt=l}const o=r.querySelector(".file-uploader__remove-btn");o!=null&&o.addEventListener("click",()=>{this.deleteFile(t)}),this.previewContainer.append(r)})}}export{g as I};
