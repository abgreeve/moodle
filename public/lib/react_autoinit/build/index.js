var __require = /* @__PURE__ */ ((x) => typeof require !== "undefined" ? require : typeof Proxy !== "undefined" ? new Proxy(x, {
  get: (a, b) => (typeof require !== "undefined" ? require : a)[b]
}) : x)(function(x) {
  if (typeof require !== "undefined") return require.apply(this, arguments);
  throw Error('Dynamic require of "' + x + '" is not supported');
});

// public/lib/react_autoinit/src/index.ts
import { React, ReactDOM } from "../../react/build/react.js";
var SELECTOR = "[data-react-component]";
var MOUNTED_FLAG = "reactMounted";
var reactUnmountMap = /* @__PURE__ */ new WeakMap();
var domReady = () => document.readyState === "loading" ? new Promise(
  (resolve) => document.addEventListener("DOMContentLoaded", resolve, { once: true })
) : Promise.resolve();
var parseProps = (el) => {
  const raw = el.getAttribute("data-react-props") || "";
  if (!raw) return {};
  try {
    return JSON.parse(raw);
  } catch (e) {
    console.error("[react_autoinit] invalid JSON", raw, e);
    return {};
  }
};
var normalizeHandlers = (props) => {
  const out = { ...props };
  for (const key of Object.keys(out)) {
    if (!key.startsWith("on")) continue;
    const value = out[key];
    if (typeof value === "string") {
      out[key] = new Function("event", value);
      continue;
    }
    if (value && typeof value === "object" && value.amd) {
      const { amd, method = null, args = [] } = value;
      out[key] = () => {
        __require([amd], (mod) => {
          const fn = method ? mod[method] : mod;
          if (typeof fn !== "function") {
            console.warn(`[react_autoinit] ${amd}.${method} is not callable`);
            return;
          }
          fn.apply(mod, args);
        });
      };
    }
  }
  return out;
};
var resolveComponent = async (componentName) => {
  if (!componentName) return null;
  try {
    const url = new URL(
      `../../../mod/book/react/build/mustache_test.js`,
      // `../build/components/${componentName}.js`,
      import.meta.url
    ).href;
    const module = await import(url);
    return module.default || module;
  } catch (e) {
    console.error(`[react_autoinit] failed to import component: ${componentName}`, e);
    return null;
  }
};
var mountReactComponent = (el, Component, props) => {
  const root = ReactDOM.createRoot(el);
  root.render(React.createElement(Component, props));
  reactUnmountMap.set(el, () => root.unmount());
};
var mountOne = async (el) => {
  if (el.dataset[MOUNTED_FLAG]) return;
  const componentName = el.getAttribute("data-react-component");
  const Component = await resolveComponent(componentName);
  if (!Component) {
    console.warn("[react_autoinit] component not found:", componentName);
    return;
  }
  const props = normalizeHandlers(parseProps(el));
  try {
    mountReactComponent(el, Component, props);
    el.dataset[MOUNTED_FLAG] = "1";
  } catch (e) {
    console.error("[react_autoinit] mount failed:", componentName, e);
  }
};
var unmountOne = (el) => {
  const unmount2 = reactUnmountMap.get(el);
  if (unmount2) {
    try {
      unmount2();
    } catch {
    }
    reactUnmountMap.delete(el);
  }
  delete el.dataset[MOUNTED_FLAG];
};
var scanAndMount = async (root) => {
  for (const el of root.querySelectorAll(SELECTOR)) {
    await mountOne(el);
  }
};
var scanAndUnmount = (root) => {
  for (const el of root.querySelectorAll(SELECTOR)) {
    unmountOne(el);
  }
};
var handleAddedNode = (node) => {
  if (!(node instanceof Element)) return;
  if (node.matches?.(SELECTOR)) mountOne(node);
  node.querySelectorAll?.(SELECTOR).forEach(mountOne);
};
var handleRemovedNode = (node) => {
  if (!(node instanceof Element)) return;
  if (node.matches?.(SELECTOR)) unmountOne(node);
  node.querySelectorAll?.(SELECTOR).forEach(unmountOne);
};
var installObserver = () => {
  const obs = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes?.forEach(handleAddedNode);
      m.removedNodes?.forEach(handleRemovedNode);
    });
  });
  obs.observe(document.documentElement, {
    childList: true,
    subtree: true
  });
  return obs;
};
var observer = null;
var resolveRoot = (selectorOrRoot) => {
  if (!selectorOrRoot) return document;
  if (typeof selectorOrRoot === "string") {
    return document.querySelector(selectorOrRoot) || document;
  }
  return selectorOrRoot;
};
var init = async (selectorOrRoot = null) => {
  await domReady();
  window.console.log("init of react auto");
  const root = resolveRoot(selectorOrRoot);
  await scanAndMount(root);
  if (!observer) observer = installObserver();
};
var unmount = (selectorOrRoot = null) => {
  const root = resolveRoot(selectorOrRoot);
  scanAndUnmount(root);
};
init();
export {
  init,
  unmount
};
