var y=(t=>typeof require<"u"?require:typeof Proxy<"u"?new Proxy(t,{get:(e,n)=>(typeof require<"u"?require:e)[n]}):t)(function(t){if(typeof require<"u")return require.apply(this,arguments);throw Error('Dynamic require of "'+t+'" is not supported')});import E from"react";import b from"react-dom/client";var s="[data-react-component]",l="reactMounted",u=new WeakMap,a=!1,_=()=>document.readyState==="loading"?new Promise(t=>document.addEventListener("DOMContentLoaded",t,{once:!0})):Promise.resolve(),v=t=>{let e=t.getAttribute("data-react-props")||"";if(!e)return{};try{return JSON.parse(e)}catch(n){return console.error("[react_autoinit] invalid JSON",e,n),{}}},$=t=>{let e={...t};for(let n of Object.keys(e)){if(!n.startsWith("on"))continue;let o=e[n];if(typeof o=="string"){e[n]=new Function("event",o);continue}if(o&&typeof o=="object"&&o.amd){let{amd:r,method:c=null,args:f=[]}=o;e[n]=()=>{y([r],i=>{let p=c?i[c]:i;if(typeof p!="function"){console.warn(`[react_autoinit] ${r}.${c} is not callable`);return}p.apply(i,f)})}}}return e},w=async t=>{if(!t)return null;try{let e=t.match(/^@([^/]+)\/(.+)$/);if(!e)return console.error("[react_autoinit] Invalid component format:",t),null;let[,n,o]=e,r;n==="core"?r=`../react/build/${o}.js`:n.startsWith("mod_")?r=`../../../mod/${n.replace("mod_","")}/react/build/${o}.js`:n.startsWith("block_")?r=`../../../blocks/${n.replace("block_","")}/react/build/${o}.js`:n.startsWith("local_")?r=`../../../local/${n.replace("local_","")}/react/build/${o}.js`:r=`../../../${n}/react/build/${o}.js`;let c=new URL(r,import.meta.url).href;return a&&console.log(`[react_autoinit] Loading: ${t} \u2192 ${c}`),await import(c)}catch(e){return console.error(`[react_autoinit] Failed to import: ${t}`,e),null}},M=(t,e,n)=>{let o=b.createRoot(t);a||o.render(E.createElement(e,n)),u.set(t,()=>o.unmount())},d=async t=>{if(t.dataset[l])return;let e=t.getAttribute("data-react-component");if(!e)return;let n=await w(e);if(!n){console.warn("[react_autoinit] Component not found:",e);return}let o=$(v(t));try{if(typeof n.init=="function"){let c=n.init(t,o);typeof c=="function"&&u.set(t,c),t.dataset[l]="1",a&&console.log(`[react_autoinit] Mounted via init(): ${e}`);return}let r=n.default;if(!r){console.warn("[react_autoinit] Module has no init() and no default export:",e);return}M(t,r,o),t.dataset[l]="1",a&&console.log(`[react_autoinit] Mounted via default: ${e}`)}catch(r){console.error("[react_autoinit] Mount failed:",e,r)}},m=t=>{let e=u.get(t);if(e){try{if(e(),a){let n=t.getAttribute("data-react-component");console.log(`[react_autoinit] Unmounted: ${n}`)}}catch(n){console.error("[react_autoinit] Error unmounting:",n)}u.delete(t)}delete t.dataset[l]},N=async t=>{let e=t.querySelectorAll(s);a&&e.length>0&&console.log(`[react_autoinit] Found ${e.length} component(s) to mount`);for(let n of e)await d(n)},A=t=>{for(let e of t.querySelectorAll(s))m(e)},O=t=>{t instanceof Element&&(t.matches?.(s)&&(a&&console.log("[react_autoinit] New component detected"),d(t)),t.querySelectorAll?.(s).forEach(d))},R=t=>{t instanceof Element&&(t.matches?.(s)&&m(t),t.querySelectorAll?.(s).forEach(m))},D=()=>{let t=new MutationObserver(e=>{e.forEach(n=>{n.addedNodes?.forEach(O),n.removedNodes?.forEach(R)})});return t.observe(document.documentElement,{childList:!0,subtree:!0}),t},g=null,h=t=>t?typeof t=="string"?document.querySelector(t)||document:t:document,S=async(t=null)=>{await _(),a&&console.log("[react_autoinit] Initializing (DEV MODE)...");let e=h(t);await N(e),g||(g=D(),a&&console.log("[react_autoinit] MutationObserver active")),a&&console.log("[react_autoinit] Ready")},q=(t=null)=>{let e=h(t);A(e)};S();export{S as init,q as unmount};
/**
 * Auto-init shim for Mustache React helper components.
 *
 * It looks for [data-react-component] in the DOM and mounts matching
 * React components from window.ReactComponents using the React APIs
 * exposed on window.
 *
 * The contract is roughly:
 * ```
 *   <div
 *     data-react-component="@core/button"
 *     data-react-props='{"label":"Save","onClick":"console.log(\"hi\")"}'
 *   ></div>
 * ```
 *
 * A MutationObserver is used so that if new HTML is injected into the page
 * (via fragments, AJAX, etc.) and it contains data-react-component nodes,
 * those nodes are mounted automatically without needing to call init() again.
 *
 * @module     core/react_autoinit
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
