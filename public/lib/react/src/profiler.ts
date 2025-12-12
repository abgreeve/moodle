import { createElement, Profiler } from "react";
import type { ComponentType, ProfilerOnRenderCallback } from "react";

const isDev = process.env.NODE_ENV === "development";

export const onRenderCallback: ProfilerOnRenderCallback = (
    id,
    phase,
    actualDuration,
    baseDuration,
    startTime,
    commitTime
) => {
    if (!isDev) return;

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

export const isProfilerEnabled = (): boolean => {
    return isDev;
};

export const getProfilerCallback = (): ProfilerOnRenderCallback | undefined => {
    return isDev ? onRenderCallback : undefined;
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
    if (!isDev) {
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
