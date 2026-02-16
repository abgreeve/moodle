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
 * Shared React Profiler helpers.
 *
 * @module     core/profiler
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { createElement, Profiler } from "react";
import type { ComponentType, ProfilerOnRenderCallback } from "react";

export const isProfilerEnabled = (): boolean => {
    return Boolean(window.M?.cfg?.reactprofiling);
};

export const onRenderCallback: ProfilerOnRenderCallback = (
    id,
    phase,
    actualDuration,
    baseDuration,
    startTime,
    commitTime
) => {
    if (!isProfilerEnabled()) return;

    console.groupCollapsed(`[${phase}] ${id} - ${actualDuration.toFixed(2)}ms`);

    console.table({
        Component: id,
        Phase: phase,
        "Duration (ms)": actualDuration.toFixed(2),
        "Base Duration (ms)": baseDuration.toFixed(2),
        "Start Time": startTime.toFixed(2),
        "Commit Time": commitTime.toFixed(2),
    });

    if (actualDuration > 16) {
        console.warn(
            `Slow render: ${actualDuration.toFixed(2)}ms (target: <16ms for 60fps)`
        );
    }

    if (actualDuration > 50) {
        console.error(
            `Very slow render: ${actualDuration.toFixed(
                2
            )}ms - Consider optimization!`
        );
    }

    console.groupEnd();
};

export const getProfilerCallback = (): ProfilerOnRenderCallback | undefined => {
    return isProfilerEnabled() ? onRenderCallback : undefined;
};

/**
 * Wraps a component with Profiler in dev mode.
 *
 * @example
 * ```tsx
 * import { withProfiler } from '@moodle/core/profiler';
 *
 * function MyComponent(props) {
 *   return <div>...</div>;
 * }
 *
 * export default withProfiler(MyComponent, 'MyComponent');
 * ```
 */
export function withProfiler<P extends object>(
    Component: ComponentType<P>,
    id?: string
): ComponentType<P> {
    if (!isProfilerEnabled()) {
        return Component;
    }

    const componentId =
        id || Component.displayName || Component.name || "Component";

    const ProfiledComponent = (props: P) =>
        createElement(
            Profiler,
            { id: componentId, onRender: onRenderCallback },
            createElement(Component, props)
        );

    ProfiledComponent.displayName = `withProfiler(${componentId})`;

    return ProfiledComponent;
}
