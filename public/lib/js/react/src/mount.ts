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
 * Shared React mount helper with optional profiling support.
 *
 * Use this for mounting React roots so profiling behavior is consistent
 * across autoinit and manually-initialised entrypoints.
 *
 * @module     core/mount
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import React, { createElement, Profiler } from "react";
import ReactDOM from "react-dom/client";
import type { ComponentType } from "react";

import { isProfilerEnabled, onRenderCallback } from "@moodle/lms/core/profiler";

type MountOptions = {
    id?: string;
};

const rootUnmountMap = new WeakMap<Element, () => void>();

/**
 * Mounts a React component to a container with optional profiling support.
 */
export function mountReactApp<P extends object>(
    container: Element,
    Component: ComponentType<P>,
    props: P,
    options: MountOptions = {}
): () => void {
    const componentId =
        options.id || Component.displayName || Component.name || "ReactApp";

    let node: any = createElement(Component, props);
    if (isProfilerEnabled()) {
        node = createElement(
            Profiler,
            { id: componentId, onRender: onRenderCallback },
            node
        );
    }

    const root = ReactDOM.createRoot(container);
    root.render(node);

    const unmount = () => {
        root.unmount();
    };

    rootUnmountMap.set(container, unmount);

    return unmount;
}

/**
 * Unmounts a previously mounted React app for a container.
 */
export function unmountReactApp(container: Element): void {
    const unmount = rootUnmountMap.get(container);
    if (unmount) {
        unmount();
        rootUnmountMap.delete(container);
    }
}
