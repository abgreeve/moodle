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
var domReady = () => {
  new Promise(
    // @ts-ignore TS can't infer resolve's type in JS
    (resolve) => {
      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => resolve(), { once: true });
      } else {
        resolve();
      }
    }
  );
};
var parseProps = (el) => {
  const raw = el.getAttribute("data-react-props") || "";
  if (!raw) {
    return {};
  }
  try {
    return JSON.parse(raw);
  } catch (e) {
    window.console.error("[react_autoinit] invalid data-react-props JSON", raw, e);
    return {};
  }
};
var normalizeHandlers = (props) => {
  const normalized = { ...props };
  Object.keys(normalized).forEach((key) => {
    const value = normalized[key];
    if (!key.startsWith("on")) {
      return;
    }
    if (typeof value === "string") {
      normalized[key] = new Function("event", value);
      return;
    }
    if (value && typeof value === "object" && value.amd) {
      const { amd, method = null, args = [] } = value;
      normalized[key] = (event) => {
        try {
          __require([amd], (mod) => {
            const fn = method && mod[method] ? mod[method] : mod;
            if (typeof fn !== "function") {
              window.console.warn(
                `[react_autoinit] ${amd} has no callable ${method || "default export"}`
              );
              return;
            }
            if (!args || args.length === 0) {
              fn.call(mod, event);
              return;
            }
            fn.apply(mod, args);
          });
        } catch (e) {
          window.console.error("[react_autoinit] failed to load AMD handler", amd, e);
        }
      };
    }
  });
  return normalized;
};
var resolveComponent = async (name) => {
  if (!name) return null;
  try {
    const url = new URL(
      `../../../mod/book/react/build/mustache_test.js`,
      import.meta.url
    ).href;
    const module = await import(url);
    return module.default || module;
  } catch (e) {
    console.error(`[react_autoinit] failed to import component: ${name}`, e);
    return null;
  }
};
var mountReactComponent = (el, Component, props) => {
  const root = ReactDOM.createRoot(el);
  root.render(React.createElement(Component, props));
  reactUnmountMap.set(el, () => root.unmount());
};
var mountOne = async (el) => {
  if (el.dataset[MOUNTED_FLAG]) {
    return;
  }
  const componentName = el.getAttribute("data-react-component");
  const Component = await resolveComponent(componentName);
  if (!Component) {
    window.console.warn("[react_autoinit] component not found in registry:", componentName);
    return;
  }
  const props = normalizeHandlers(parseProps(el));
  try {
    mountReactComponent(el, Component, props);
    el.dataset[MOUNTED_FLAG] = "1";
  } catch (e) {
    window.console.error("[react_autoinit] mount failed:", componentName, e);
  }
};
var unmountOne = (el) => {
  const unmount2 = reactUnmountMap.get(el);
  if (unmount2) {
    try {
      unmount2();
    } catch (e) {
    }
    reactUnmountMap.delete(el);
  }
  delete el.dataset[MOUNTED_FLAG];
};
var scanAndMount = async (root) => {
  const scope = root || document;
  scope.querySelectorAll(SELECTOR).forEach(
    async (el) => {
      await mountOne(
        /** @type {HTMLElement} */
        el
      );
    }
  );
};
var scanAndUnmount = (root) => {
  const scope = root || document;
  scope.querySelectorAll(SELECTOR).forEach(
    (el) => {
      unmountOne(
        /** @type {HTMLElement} */
        el
      );
    }
  );
};
var handleAddedNode = (node) => {
  if (!(node instanceof Element)) {
    return;
  }
  if (node.matches && node.matches(SELECTOR)) {
    mountOne(
      /** @type {HTMLElement} */
      node
    );
  }
  if (node.querySelectorAll) {
    node.querySelectorAll(SELECTOR).forEach(
      (el) => {
        mountOne(
          /** @type {HTMLElement} */
          el
        );
      }
    );
  }
};
var handleRemovedNode = (node) => {
  if (!(node instanceof Element)) {
    return;
  }
  if (node.matches && node.matches(SELECTOR)) {
    unmountOne(
      /** @type {HTMLElement} */
      node
    );
  }
  if (node.querySelectorAll) {
    node.querySelectorAll(SELECTOR).forEach(
      (el) => {
        unmountOne(
          /** @type {HTMLElement} */
          el
        );
      }
    );
  }
};
var installObserver = () => {
  const obs = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.addedNodes) {
        mutation.addedNodes.forEach(handleAddedNode);
      }
      if (mutation.removedNodes) {
        mutation.removedNodes.forEach(handleRemovedNode);
      }
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
var init = async (selectorOrRoot) => {
  await domReady();
  const root = resolveRoot(selectorOrRoot);
  await scanAndMount(root);
  if (!observer) {
    observer = installObserver();
  }
};
var unmount = (selectorOrRoot) => {
  const root = resolveRoot(selectorOrRoot);
  scanAndUnmount(root);
};
init();
export {
  init,
  unmount
};
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
 * The above would mount the Button component registered as
 * window.ReactComponents["@core/button"] with the given props.
 *
 * A MutationObserver is used so that if new HTML is injected into the page
 * (via fragments, AJAX, etc.) and it contains data-react-component nodes,
 * those nodes are mounted automatically without needing to call init() again.
 *
 * @module     core/react_autoinit
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
