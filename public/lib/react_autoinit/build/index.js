var g=(t=>typeof require<"u"?require:typeof Proxy<"u"?new Proxy(t,{get:(e,n)=>(typeof require<"u"?require:e)[n]}):t)(function(t){if(typeof require<"u")return require.apply(this,arguments);throw Error('Dynamic require of "'+t+'" is not supported')});import s from"react";import _ from"react-dom/client";import{onRenderCallback as v,isProfilerEnabled as b}from"@moodle/lms/core/profiler";var i="[data-react-component]",u="reactMounted",l=new WeakMap,a=b(),w=()=>document.readyState==="loading"?new Promise(t=>document.addEventListener("DOMContentLoaded",t,{once:!0})):Promise.resolve(),M=t=>{let e=t.getAttribute("data-react-props")||"";if(!e)return{};try{return JSON.parse(e)}catch(n){return console.error("[react_autoinit] invalid JSON",e,n),{}}},$=t=>{let e={...t};for(let n of Object.keys(e)){if(!n.startsWith("on"))continue;let o=e[n];if(typeof o=="string"){e[n]=new Function("event",o);continue}if(o&&typeof o=="object"&&o.amd){let{amd:r,method:c=null,args:p=[]}=o;e[n]=()=>{g([r],d=>{let y=c?d[c]:d;if(typeof y!="function"){console.warn(`[react_autoinit] ${r}.${c} is not callable`);return}y.apply(d,p)})}}}return e},A=async t=>{if(!t)return null;try{let e=t.match(/^@([^/]+)\/(.+)$/);if(!e)return console.error("[react_autoinit] Invalid component format:",t),null;let[,n,o]=e,c=`@moodle/lms/${n==="core"||n.startsWith("core_")||n.includes("_")?n:`core_${n}`}/${o}`;return a&&console.log(`[react_autoinit] Loading: ${t} \u2192 ${c}`),await import(c)}catch(e){return console.error(`[react_autoinit] Failed to import: ${t}`,e),null}},R=(t,e,n)=>{let o=_.createRoot(t);if(a){let r=t.getAttribute("data-react-component")||"Unknown";o.render(s.createElement(s.Profiler,{id:r,onRender:v},s.createElement(e,n)))}else o.render(s.createElement(e,n));l.set(t,()=>o.unmount())},m=async t=>{if(t.dataset[u])return;let e=t.getAttribute("data-react-component");if(!e)return;let n=await A(e);if(!n){console.warn("[react_autoinit] Component not found:",e);return}let o=$(M(t));try{if(typeof n.init=="function"){let c=n.init(t,o);typeof c=="function"&&l.set(t,c),t.dataset[u]="1",a&&console.log(`[react_autoinit] Mounted via init(): ${e}`);return}let r=n.default;if(!r){console.warn("[react_autoinit] Module has no init() and no default export:",e);return}R(t,r,o),t.dataset[u]="1",a&&console.log(`[react_autoinit] Mounted via default: ${e}`)}catch(r){console.error("[react_autoinit] Mount failed:",e,r)}},f=t=>{let e=l.get(t);if(e){try{if(e(),a){let n=t.getAttribute("data-react-component");console.log(`[react_autoinit] Unmounted: ${n}`)}}catch(n){console.error("[react_autoinit] Error unmounting:",n)}l.delete(t)}delete t.dataset[u]},O=async t=>{let e=t.querySelectorAll(i);a&&e.length>0&&console.log(`[react_autoinit] Found ${e.length} component(s) to mount`);for(let n of e)await m(n)},N=t=>{for(let e of t.querySelectorAll(i))f(e)},S=t=>{t instanceof Element&&(t.matches?.(i)&&(a&&console.log("[react_autoinit] New component detected"),m(t)),t.querySelectorAll?.(i).forEach(m))},P=t=>{t instanceof Element&&(t.matches?.(i)&&f(t),t.querySelectorAll?.(i).forEach(f))},q=()=>{let t=new MutationObserver(e=>{e.forEach(n=>{n.addedNodes?.forEach(S),n.removedNodes?.forEach(P)})});return t.observe(document.documentElement,{childList:!0,subtree:!0}),t},E=null,h=t=>t?typeof t=="string"?document.querySelector(t)||document:t:document,C=async(t=null)=>{await w(),a&&console.log("[react_autoinit] Initializing (profiling enabled)...");let e=h(t);await O(e),E||(E=q(),a&&console.log("[react_autoinit] MutationObserver active")),a&&console.log("[react_autoinit] Ready")},F=(t=null)=>{let e=h(t);N(e)};C();export{C as init,F as unmount};
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
