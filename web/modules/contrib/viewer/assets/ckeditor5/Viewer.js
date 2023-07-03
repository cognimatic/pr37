!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.CKEditor5=t():(e.CKEditor5=e.CKEditor5||{},e.CKEditor5.Viewer=t())}(self,(()=>(()=>{var e={"ckeditor5/src/core.js":(e,t,r)=>{e.exports=r("dll-reference CKEditor5.dll")("./src/core.js")},"ckeditor5/src/engine.js":(e,t,r)=>{e.exports=r("dll-reference CKEditor5.dll")("./src/engine.js")},"ckeditor5/src/ui.js":(e,t,r)=>{e.exports=r("dll-reference CKEditor5.dll")("./src/ui.js")},"ckeditor5/src/widget.js":(e,t,r)=>{e.exports=r("dll-reference CKEditor5.dll")("./src/widget.js")},"dll-reference CKEditor5.dll":e=>{"use strict";e.exports=CKEditor5.dll}},t={};function r(i){var o=t[i];if(void 0!==o)return o.exports;var s=t[i]={exports:{}};return e[i](s,s.exports,r),s.exports}r.d=(e,t)=>{for(var i in t)r.o(t,i)&&!r.o(e,i)&&Object.defineProperty(e,i,{enumerable:!0,get:t[i]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t);var i={};return(()=>{"use strict";r.d(i,{default:()=>w});var e=r("ckeditor5/src/core.js"),t=r("ckeditor5/src/widget.js");class o extends e.Command{execute(e){const t=this.editor.plugins.get("ViewerEditing"),r=Object.entries(t.attrs).reduce(((e,[t,r])=>(e[r]=t,e)),{}),i=Object.keys(e).reduce(((t,i)=>(r[i]&&(t[r[i]]=e[i]),t)),{});this.editor.model.change((e=>{this.editor.model.insertContent(function(e,t){return e.createElement("Viewer",t)}(e,i))}))}refresh(){const e=this.editor.model,t=e.document.selection,r=e.schema.findAllowedParent(t.getFirstPosition(),"Viewer");this.isEnabled=null!==r}}class s extends e.Plugin{static get requires(){return[t.Widget]}init(){this.attrs={ViewerId:"data-viewer"};const e=this.editor.config.get("Viewer");if(!e)return;const{previewURL:t,themeError:r}=e;this.previewUrl=t,this.themeError=r||`\n      <p>${Drupal.t("An error occurred while trying to preview the Viewer. Please save your work and reload this page.")}<p>\n    `,this._defineSchema(),this._defineConverters(),this.editor.commands.add("Viewer",new o(this.editor))}async _fetchPreview(e){const t={viewer:e.getAttribute("ViewerId")},r=await fetch(`${this.previewUrl}?${new URLSearchParams(t)}`);return r.ok?await r.text():this.themeError}_defineSchema(){this.editor.model.schema.register("Viewer",{allowWhere:"$block",isObject:!0,isContent:!0,isBlock:!0,allowAttributes:Object.keys(this.attrs)}),this.editor.editing.view.domConverter.blockElements.push("viewer")}_defineConverters(){const e=this.editor.conversion;e.for("upcast").elementToElement({model:"Viewer",view:{name:"viewer"}}),e.for("dataDowncast").elementToElement({model:"Viewer",view:{name:"viewer"}}),e.for("editingDowncast").elementToElement({model:"Viewer",view:(e,{writer:r})=>{const i=r.createContainerElement("figure");return(0,t.toWidget)(i,r,{label:Drupal.t("Viewer")})}}).add((e=>(e.on("attribute:ViewerId:Viewer",((e,t,r)=>{const i=r.writer,o=t.item,s=r.mapper.toViewElement(t.item),n=i.createRawElement("div",{"data-viewer-preview":"loading",class:"viewer-preview"});i.insert(i.createPositionAt(s,0),n),this._fetchPreview(o).then((e=>{n&&this.editor.editing.view.change((t=>{const r=t.createRawElement("div",{class:"viewer-preview","data-viewer-preview":"ready"},(t=>{t.innerHTML=e}));t.insert(t.createPositionBefore(n),r),t.remove(n)}))}))})),e))),Object.keys(this.attrs).forEach((t=>{const r={model:{key:t,name:"Viewer"},view:{name:"viewer",key:this.attrs[t]}};e.for("dataDowncast").attributeToAttribute(r),e.for("upcast").attributeToAttribute(r)}))}static get pluginName(){return"ViewerEditing"}}var n=r("ckeditor5/src/ui.js");var d=r("ckeditor5/src/engine.js");class a extends d.DomEventObserver{constructor(e){super(e),this.domEventType="dblclick"}onDomEvent(e){this.fire(e.type,e)}}class c extends e.Plugin{init(){const e=this.editor,t=this.editor.config.get("Viewer");if(!t)return;const{dialogURL:r,openDialog:i,dialogSettings:o={}}=t;if(!r||"function"!=typeof i)return;e.ui.componentFactory.add("Viewer",(t=>{const s=e.commands.get("Viewer"),d=new n.ButtonView(t);return d.set({label:Drupal.t("Viewer"),icon:'<?xml version="1.0" encoding="utf-8"?>\x3c!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools --\x3e\r\n<svg fill="#000000" width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M2.062,11.346a.99.99,0,0,1,0-.691C3.773,6,7.674,3,12,3s8.227,3,9.938,7.655a.987.987,0,0,1,0,.69,13.339,13.339,0,0,1-1.08,2.264,1,1,0,1,1-1.715-1.028A11.3,11.3,0,0,0,19.928,11C18.451,7.343,15.373,5,12,5S5.549,7.343,4.072,11a9.315,9.315,0,0,0,6.167,5.787,1,1,0,0,1-.478,1.942A11.393,11.393,0,0,1,2.062,11.346ZM16,11a4,4,0,0,0-5.577-3.675,1.5,1.5,0,1,1-2.1,2.1A4,4,0,1,0,16,11Zm1.5,10a1,1,0,0,0,1-1V18.5H20a1,1,0,0,0,0-2H18.5V15a1,1,0,0,0-2,0v1.5H15a1,1,0,0,0,0,2h1.5V20A1,1,0,0,0,17.5,21Z"/></svg>',tooltip:!0}),d.bind("isOn","isEnabled").to(s,"value","isEnabled"),this.listenTo(d,"execute",(()=>{i(r,(({attributes:t})=>{e.execute("Viewer",t)}),o)})),d}));const s=e.editing.view,d=s.document;s.addObserver(a),e.listenTo(d,"dblclick",((t,s)=>{const n=e.editing.mapper.toModelElement(s.target);if(n&&void 0!==n.name&&"Viewer"===n.name){const t={viewer:n.getAttribute("ViewerId")};i(`${r}?${new URLSearchParams(t)}`,(({attributes:t})=>{e.execute("Viewer",t)}),o)}}))}}class l extends e.Plugin{static get requires(){return[s,c]}static get pluginName(){return"Viewer"}}const w={Viewer:l}})(),i=i.default})()));