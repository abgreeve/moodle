// public/lib/react/src/react_autoinit.js  (ESM)

import { React, ReactDOMClient } from '@moodle/core/react';

const SELECTOR = '[data-react-component]';
const MOUNTED_FLAG = 'reactMounted';
const reactUnmountMap = new WeakMap();

/**
 * DOM ready promise.
 */
const domReady = () =>
    document.readyState === 'loading'
        ? new Promise((resolve) =>
              document.addEventListener('DOMContentLoaded', resolve, { once: true })
          )
        : Promise.resolve();

/**
 * Safe JSON parsing from data-react-props.
 */
const parseProps = (el) => {
    const raw = el.getAttribute('data-react-props') || '';
    if (!raw) return {};
    try {
        return JSON.parse(raw);
    } catch (e) {
        console.error('[react_autoinit] invalid JSON', raw, e);
        return {};
    }
};

/**
 * Normalise inline handlers + AMD handlers.
 */
const normalizeHandlers = (props) => {
    const out = { ...props };

    for (const key of Object.keys(out)) {
        if (!key.startsWith('on')) continue;

        const value = out[key];

        // Inline string handler: onClick="console.log('hi')"
        if (typeof value === 'string') {
            out[key] = new Function('event', value);
            continue;
        }

        // AMD handler: { amd: "core/notification", method: "alert", args: [...] }
        if (value && typeof value === 'object' && value.amd) {
            const { amd, method = null, args = [] } = value;

            out[key] = () => {
                // eslint-disable-next-line no-undef
                require([amd], (mod) => {
                    const fn = method ? mod[method] : mod;
                    if (typeof fn !== 'function') {
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

/**
 * Dynamic import of a component using real ESM.
 */
const resolveComponent = async (componentName) => {
    if (!componentName) return null;

    try {
        const url = new URL(
            `../build/components/${componentName}.js`,
            import.meta.url
        ).href;

        const module = await import(url);
        return module.default || module;
    } catch (e) {
        console.error(`[react_autoinit] failed to import component: ${componentName}`, e);
        return null;
    }
};

/**
 * Mount a single React component.
 */
const mountReactComponent = (el, Component, props) => {
    const root = ReactDOMClient.createRoot(el);
    root.render(React.createElement(Component, props));

    reactUnmountMap.set(el, () => root.unmount());
};

/**
 * Mount an element with the `data-react-component` attribute.
 */
const mountOne = async (el) => {
    if (el.dataset[MOUNTED_FLAG]) return;

    const componentName = el.getAttribute('data-react-component');
    const Component = await resolveComponent(componentName);

    if (!Component) {
        console.warn('[react_autoinit] component not found:', componentName);
        return;
    }

    const props = normalizeHandlers(parseProps(el));

    try {
        mountReactComponent(el, Component, props);
        el.dataset[MOUNTED_FLAG] = '1';
    } catch (e) {
        console.error('[react_autoinit] mount failed:', componentName, e);
    }
};

/**
 * Unmount a single element.
 */
const unmountOne = (el) => {
    const unmount = reactUnmountMap.get(el);
    if (unmount) {
        try {
            unmount();
        } catch {}
        reactUnmountMap.delete(el);
    }
    delete el.dataset[MOUNTED_FLAG];
};

const scanAndMount = async (root) => {
    for (const el of root.querySelectorAll(SELECTOR)) {
        await mountOne(el);
    }
};

const scanAndUnmount = (root) => {
    for (const el of root.querySelectorAll(SELECTOR)) {
        unmountOne(el);
    }
};

/**
 * MutationObserver support.
 */
const handleAddedNode = (node) => {
    if (!(node instanceof Element)) return;

    if (node.matches?.(SELECTOR)) mountOne(node);
    node.querySelectorAll?.(SELECTOR).forEach(mountOne);
};

const handleRemovedNode = (node) => {
    if (!(node instanceof Element)) return;

    if (node.matches?.(SELECTOR)) unmountOne(node);
    node.querySelectorAll?.(SELECTOR).forEach(unmountOne);
};

const installObserver = () => {
    const obs = new MutationObserver((mutations) => {
        mutations.forEach((m) => {
            m.addedNodes?.forEach(handleAddedNode);
            m.removedNodes?.forEach(handleRemovedNode);
        });
    });

    obs.observe(document.documentElement, {
        childList: true,
        subtree: true,
    });

    return obs;
};

let observer = null;

const resolveRoot = (selectorOrRoot) => {
    if (!selectorOrRoot) return document;
    if (typeof selectorOrRoot === 'string') {
        return document.querySelector(selectorOrRoot) || document;
    }
    return selectorOrRoot;
};

/**
 * Main init.
 */
export const init = async (selectorOrRoot = null) => {
    await domReady();

    const root = resolveRoot(selectorOrRoot);
    await scanAndMount(root);

    if (!observer) observer = installObserver();
};

/**
 * Manual unmount API.
 */
export const unmount = (selectorOrRoot = null) => {
    const root = resolveRoot(selectorOrRoot);
    scanAndUnmount(root);
};
