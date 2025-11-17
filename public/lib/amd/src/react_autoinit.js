// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

const SELECTOR = '[data-react-component]';
const MOUNTED_FLAG = 'reactMounted';

// For each DOM element we mount into, we keep a function that knows how
// to unmount its React component later.
const reactUnmountMap = new WeakMap();

/**
 * Run code once the DOM is ready.
 *
 * @return {Promise<void>}
 */
const domReady = () =>
  new Promise(
    // @ts-ignore TS can't infer resolve's type in JS
    (resolve) => {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => resolve(), { once: true });
      } else {
        resolve();
      }
    }
  );

/**
 * Read and parse JSON from data-react-props.
 *
 * @param {HTMLElement} el
 * @return {Object}
 */
const parseProps = (el) => {
  const raw = el.getAttribute('data-react-props') || '';

  if (!raw) {
    return {};
  }

  try {
    return JSON.parse(raw);
  } catch (e) {
    window.console.error('[react_autoinit] invalid data-react-props JSON', raw, e);
    return {};
  }
};

/**
 * Wrap inline string handlers and AMD references into real functions.
 *
 * Supported:
 *  - "onClick": "console.log('hi')"
 *  - "onClick": { "amd": "core/notification", "method": "alert", "args": ["t", "m"] }
 *
 * Rules:
 *  - String handler:
 *      - becomes function(event) { /* string body *\/ }
 *  - AMD handler:
 *      - no args  → fn(event)
 *      - with args → fn(...args)  (event is NOT passed)
 *
 * @param {Object} props
 * @return {Object}
 */
const normalizeHandlers = (props) => {
  const normalized = { ...props };

  Object.keys(normalized).forEach((key) => {
    const value = normalized[key];

    // Only touch handler-like props (onClick, onChange, etc.).
    if (!key.startsWith('on')) {
      return;
    }

    if (typeof value === 'string') {
      // eslint-disable-next-line no-new-func
      normalized[key] = new Function('event', value);
      return;
    }

    // AMD reference object: { amd, method?, args? }
    if (value && typeof value === 'object' && value.amd) {
      const { amd, method = null, args = [] } = value;

      normalized[key] = (event) => {
        try {
          // @ts-ignore
          require([amd], (mod) => {
            const fn = method && mod[method] ? mod[method] : mod;

            if (typeof fn !== 'function') {
              window.console.warn(
                `[react_autoinit] ${amd} has no callable ${method || 'default export'}`
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
          window.console.error('[react_autoinit] failed to load AMD handler', amd, e);
        }
      };
    }
  });

  return normalized;
};

/**
 * Resolve a component from the global registry.
 *
 * Expect something like: `window.ReactComponents["@core/button"] = Button;`
 *
 * @param {string|null} name
 * @return {Function|null}
 */
const resolveComponent = (name) => {
  if (!name) {
    return null;
  }

  // @ts-ignore ReactComponents is injected globally by our React bundle
  const registry = window.ReactComponents || {};
  const mod = registry[name];

  if (!mod) {
    return null;
  }

  // Allow both ES module default exports and plain functions.
  return mod.default || mod;
};

/**
 * Get React APIs from the global window (provided by your bundle).
 *
 * @return {{React: any, ReactDOMClient: any}}
 */
const getReactAPIs = () => ({
  React: window.React,
  // @ts-ignore ReactDOMClient is attached globally from the React bundle
  ReactDOMClient: window.ReactDOMClient,
});

/**
 * Choose the mount strategy (React >= 18 only).
 *
 * @param {HTMLElement} el
 * @param {Function} Component
 * @param {Object} props
 * @param {WeakMap<HTMLElement, Function>} unmountMap
 * @param {any} apis
 */
const mountReactComponent = (el, Component, props, unmountMap, apis) => {
  // We only support React >= 18 now.
  const { React, ReactDOMClient } = apis;

  if (!React || !ReactDOMClient || typeof ReactDOMClient.createRoot !== 'function') {
    window.console.error(
      '[react_autoinit] React >= 18 with ReactDOMClient.createRoot is required'
    );
    return;
  }

  const root = ReactDOMClient.createRoot(el);
  root.render(React.createElement(Component, props));

  // Keep the cleanup function for later.
  unmountMap.set(el, () => root.unmount());
};

/**
 * Mount a single element with data-react-component.
 *
 * @param {HTMLElement} el
 */
const mountOne = (el) => {
  if (el.dataset[MOUNTED_FLAG]) {
    // Already mounted, nothing to do.
    return;
  }

  const componentName = el.getAttribute('data-react-component');
  const Component = resolveComponent(componentName);

  if (!Component) {
    window.console.warn('[react_autoinit] component not found in registry:', componentName);
    return;
  }

  const apis = getReactAPIs();
  const { React } = apis;

  if (!React) {
    window.console.error('[react_autoinit] React missing on window');
    return;
  }

  const props = normalizeHandlers(parseProps(el));

  try {
    mountReactComponent(el, Component, props, reactUnmountMap, apis);
    el.dataset[MOUNTED_FLAG] = '1';
  } catch (e) {
    window.console.error('[react_autoinit] mount failed:', componentName, e);
  }
};

/**
 * Unmount a single element if it was previously mounted.
 *
 * @param {HTMLElement} el
 */
const unmountOne = (el) => {
  const unmount = reactUnmountMap.get(el);

  if (unmount) {
    try {
      unmount();
    } catch (e) {
      // If unmount complains, we just move on.
    }
    reactUnmountMap.delete(el);
  }

  delete el.dataset[MOUNTED_FLAG];
};

/**
 * Scan inside the given root and mount all matching elements.
 *
 * @param {HTMLElement|Document} root
 */
const scanAndMount = (root) => {
  const scope = root || document;
  scope.querySelectorAll(SELECTOR).forEach(
    (el) => {
      // We only mount into HTMLElements.
      mountOne(/** @type {HTMLElement} */ (el));
    }
  );
};

/**
 * Scan inside the given root and unmount all matching elements.
 *
 * @param {HTMLElement|Document} root
 */
const scanAndUnmount = (root) => {
  const scope = root || document;
  scope.querySelectorAll(SELECTOR).forEach(
    (el) => {
      unmountOne(/** @type {HTMLElement} */ (el));
    }
  );
};

/**
 * Handle nodes added to the DOM (for MutationObserver).
 *
 * @param {Node} node
 */
const handleAddedNode = (node) => {
  if (!(node instanceof Element)) {
    return;
  }

  // If the node itself is a React mount point.
  if (node.matches && node.matches(SELECTOR)) {
    mountOne(/** @type {HTMLElement} */ (node));
  }

  // Or if it contains any mount points deeper inside.
  if (node.querySelectorAll) {
    node.querySelectorAll(SELECTOR).forEach(
      (el) => {
        mountOne(/** @type {HTMLElement} */ (el));
      }
    );
  }
};

/**
 * Handle nodes removed from the DOM (for MutationObserver).
 *
 * @param {Node} node
 */
const handleRemovedNode = (node) => {
  if (!(node instanceof Element)) {
    return;
  }

  if (node.matches && node.matches(SELECTOR)) {
    unmountOne(/** @type {HTMLElement} */ (node));
  }

  if (node.querySelectorAll) {
    node.querySelectorAll(SELECTOR).forEach(
      (el) => {
        unmountOne(/** @type {HTMLElement} */ (el));
      }
    );
  }
};

/**
 * Install a MutationObserver to automatically mount/unmount any
 * data-react-component nodes that are added/removed from the DOM.
 *
 * @return {MutationObserver}
 */
const installObserver = () => {
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
    subtree: true,
  });

  return obs;
};

let observer = null;

/**
 * Resolve a selector or element into a root node.
 *
 * @param {string|HTMLElement|Document|null|undefined} selectorOrRoot
 * @return {HTMLElement|Document}
 */
const resolveRoot = (selectorOrRoot) => {
  if (!selectorOrRoot) {
    return document;
  }

  if (typeof selectorOrRoot === 'string') {
    const el = document.querySelector(selectorOrRoot);
    if (!el) {
      window.console.warn('[react_autoinit] selector not found:', selectorOrRoot);
      return document;
    }
    return /** @type {HTMLElement} */ (el);
  }

  return selectorOrRoot;
};

/**
 * initialise React components inside an optional selector.
 *
 * Usage:
 *   import { init } from 'core/react_autoinit';
 *   init();               // whole document
 *   init('#region-main'); // or a specific container
 *
 * @param {string|HTMLElement|Document|null} selectorOrRoot
 * @return {Promise<void>}
 */
export const init = async (selectorOrRoot) => {
  await domReady();

  const root = resolveRoot(selectorOrRoot);
  scanAndMount(root);

  // Lazy-install the observer so we only pay the cost once.
  if (!observer) {
    observer = installObserver();
  }

  // // Cleanup on full page unload.
  // window.addEventListener(
  //   'beforeunload',
  //   () => {
  //     scanAndUnmount(document);
  //   },
  //   { once: true }
  // );
};

/**
 * Unmount React components inside a given region.
 *
 * @param {string|HTMLElement|Document|null} selectorOrRoot
 */
export const unmount = (selectorOrRoot) => {
  const root = resolveRoot(selectorOrRoot);
  scanAndUnmount(root);
};
