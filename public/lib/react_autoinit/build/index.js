var y=(t=>typeof require<"u"?require:typeof Proxy<"u"?new Proxy(t,{get:(n,e)=>(typeof require<"u"?require:n)[e]}):t)(function(t){if(typeof require<"u")return require.apply(this,arguments);throw Error('Dynamic require of "'+t+'" is not supported')});import{isProfilerEnabled as h}from"@moodle/lms/core/profiler";import{mountReactApp as _,unmountReactApp as v}from"@moodle/lms/core/mount";var i="[data-react-component]",s="reactMounted",u=new WeakMap,c=h(),b=()=>document.readyState==="loading"?new Promise(t=>document.addEventListener("DOMContentLoaded",t,{once:!0})):Promise.resolve(),w=t=>{let n=t.getAttribute("data-react-props")||"";if(!n)return{};try{return JSON.parse(n)}catch(e){return console.error("[react_autoinit] invalid JSON",n,e),{}}},A=t=>{let n={...t};for(let e of Object.keys(n)){if(!e.startsWith("on"))continue;let o=n[e];if(typeof o=="string"){n[e]=new Function("event",o);continue}if(o&&typeof o=="object"&&o.amd){let{amd:r,method:a=null,args:f=[]}=o;n[e]=()=>{y([r],l=>{let p=a?l[a]:l;if(typeof p!="function"){console.warn(`[react_autoinit] ${r}.${a} is not callable`);return}p.apply(l,f)})}}}return n},M=async t=>{if(!t)return null;try{let n=t.match(/^@([^/]+)\/(.+)$/);if(!n)return console.error("[react_autoinit] Invalid component format:",t),null;let[,e,o]=n,a=`@moodle/lms/${e==="core"||e.startsWith("core_")||e.includes("_")?e:`core_${e}`}/${o}`;return c&&console.log(`[react_autoinit] Loading: ${t} \u2192 ${a}`),await import(a)}catch(n){return console.error(`[react_autoinit] Failed to import: ${t}`,n),null}},$=(t,n,e)=>{let o=t.getAttribute("data-react-component")||"Unknown",r=_(t,n,e,{id:o});u.set(t,r)},d=async t=>{if(t.dataset[s])return;let n=t.getAttribute("data-react-component");if(!n)return;let e=await M(n);if(!e){console.warn("[react_autoinit] Component not found:",n);return}let o=A(w(t));try{if(typeof e.init=="function"){let a=e.init(t,o);typeof a=="function"&&u.set(t,a),t.dataset[s]="1",c&&console.log(`[react_autoinit] Mounted via init(): ${n}`);return}let r=e.default;if(!r){console.warn("[react_autoinit] Module has no init() and no default export:",n);return}$(t,r,o),t.dataset[s]="1",c&&console.log(`[react_autoinit] Mounted via default: ${n}`)}catch(r){console.error("[react_autoinit] Mount failed:",n,r)}},m=t=>{let n=u.get(t)??(()=>v(t));if(n){try{if(n(),c){let e=t.getAttribute("data-react-component");console.log(`[react_autoinit] Unmounted: ${e}`)}}catch(e){console.error("[react_autoinit] Error unmounting:",e)}u.delete(t)}delete t.dataset[s]},N=async t=>{let n=t.querySelectorAll(i);c&&n.length>0&&console.log(`[react_autoinit] Found ${n.length} component(s) to mount`);for(let e of n)await d(e)},O=t=>{for(let n of t.querySelectorAll(i))m(n)},R=t=>{t instanceof Element&&(t.matches?.(i)&&(c&&console.log("[react_autoinit] New component detected"),d(t)),t.querySelectorAll?.(i).forEach(d))},S=t=>{t instanceof Element&&(t.matches?.(i)&&m(t),t.querySelectorAll?.(i).forEach(m))},q=()=>{let t=new MutationObserver(n=>{n.forEach(e=>{e.addedNodes?.forEach(R),e.removedNodes?.forEach(S)})});return t.observe(document.documentElement,{childList:!0,subtree:!0}),t},g=null,E=t=>t?typeof t=="string"?document.querySelector(t)||document:t:document,C=async(t=null)=>{await b(),c&&console.log("[react_autoinit] Initializing (profiling enabled)...");let n=E(t);await N(n),g||(g=q(),c&&console.log("[react_autoinit] MutationObserver active")),c&&console.log("[react_autoinit] Ready")},U=(t=null)=>{let n=E(t);O(n)};C();export{C as init,U as unmount};
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
