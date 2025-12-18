var g=(t=>typeof require<"u"?require:typeof Proxy<"u"?new Proxy(t,{get:(e,n)=>(typeof require<"u"?require:e)[n]}):t)(function(t){if(typeof require<"u")return require.apply(this,arguments);throw Error('Dynamic require of "'+t+'" is not supported')});import{React as l,ReactDOM as h}from"../../react/build/react.js";import{onRenderCallback as _,isProfilerEnabled as v}from"../../react/build/profiler.js";var s="[data-react-component]",u="reactMounted",d=new WeakMap,a=v(),$=()=>document.readyState==="loading"?new Promise(t=>document.addEventListener("DOMContentLoaded",t,{once:!0})):Promise.resolve(),w=t=>{let e=t.getAttribute("data-react-props")||"";if(!e)return{};try{return JSON.parse(e)}catch(n){return console.error("[react_autoinit] invalid JSON",e,n),{}}},M=t=>{let e={...t};for(let n of Object.keys(e)){if(!n.startsWith("on"))continue;let o=e[n];if(typeof o=="string"){e[n]=new Function("event",o);continue}if(o&&typeof o=="object"&&o.amd){let{amd:r,method:c=null,args:p=[]}=o;e[n]=()=>{g([r],i=>{let y=c?i[c]:i;if(typeof y!="function"){console.warn(`[react_autoinit] ${r}.${c} is not callable`);return}y.apply(i,p)})}}}return e},N=async t=>{if(!t)return null;try{let e=t.match(/^@([^/]+)\/(.+)$/);if(!e)return console.error("[react_autoinit] Invalid component format:",t),null;let[,n,o]=e,r;n==="core"?r=`../react/build/${o}.js`:n.startsWith("mod_")?r=`../../../mod/${n.replace("mod_","")}/react/build/${o}.js`:n.startsWith("block_")?r=`../../../blocks/${n.replace("block_","")}/react/build/${o}.js`:n.startsWith("local_")?r=`../../../local/${n.replace("local_","")}/react/build/${o}.js`:r=`../../../${n}/react/build/${o}.js`;let c=new URL(r,import.meta.url).href;return a&&console.log(`[react_autoinit] Loading: ${t} \u2192 ${c}`),await import(c)}catch(e){return console.error(`[react_autoinit] Failed to import: ${t}`,e),null}},R=(t,e,n)=>{let o=h.createRoot(t);if(a){let r=t.getAttribute("data-react-component")||"Unknown";o.render(l.createElement(l.Profiler,{id:r,onRender:_},l.createElement(e,n)))}else o.render(l.createElement(e,n));d.set(t,()=>o.unmount())},m=async t=>{if(t.dataset[u])return;let e=t.getAttribute("data-react-component");if(!e)return;let n=await N(e);if(!n){console.warn("[react_autoinit] Component not found:",e);return}let o=M(w(t));try{if(typeof n.init=="function"){let c=n.init(t,o);typeof c=="function"&&d.set(t,c),t.dataset[u]="1",a&&console.log(`[react_autoinit] Mounted via init(): ${e}`);return}let r=n.default;if(!r){console.warn("[react_autoinit] Module has no init() and no default export:",e);return}R(t,r,o),t.dataset[u]="1",a&&console.log(`[react_autoinit] Mounted via default: ${e}`)}catch(r){console.error("[react_autoinit] Mount failed:",e,r)}},f=t=>{let e=d.get(t);if(e){try{if(e(),a){let n=t.getAttribute("data-react-component");console.log(`[react_autoinit] Unmounted: ${n}`)}}catch(n){console.error("[react_autoinit] Error unmounting:",n)}d.delete(t)}delete t.dataset[u]},A=async t=>{let e=t.querySelectorAll(s);a&&e.length>0&&console.log(`[react_autoinit] Found ${e.length} component(s) to mount`);for(let n of e)await m(n)},O=t=>{for(let e of t.querySelectorAll(s))f(e)},k=t=>{t instanceof Element&&(t.matches?.(s)&&(a&&console.log("[react_autoinit] New component detected"),m(t)),t.querySelectorAll?.(s).forEach(m))},D=t=>{t instanceof Element&&(t.matches?.(s)&&f(t),t.querySelectorAll?.(s).forEach(f))},S=()=>{let t=new MutationObserver(e=>{e.forEach(n=>{n.addedNodes?.forEach(k),n.removedNodes?.forEach(D)})});return t.observe(document.documentElement,{childList:!0,subtree:!0}),t},E=null,b=t=>t?typeof t=="string"?document.querySelector(t)||document:t:document,P=async(t=null)=>{await $(),a&&console.log("[react_autoinit] Initializing (DEV MODE)...");let e=b(t);await A(e),E||(E=S(),a&&console.log("[react_autoinit] MutationObserver active")),a&&console.log("[react_autoinit] Ready")},C=(t=null)=>{let e=b(t);O(e)};P();export{P as init,C as unmount};
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
