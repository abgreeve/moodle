import type { ComponentType } from "react";

/**
 * Keys for the component registry.
 * Examples: "@core/button", "@core/input", etc.
 */
export type ReactComponentKey = string;

/**
 * Any React component type.
 */
export type ReactComponentType = ComponentType<any>;

/**
 * Global registry mapping keys to React components.
 * Example:
 * {
 *   "@core/button": Button,
 *   "@core/input": Input,
 * }
 */
export type ReactComponentsRegistry = Record<
  ReactComponentKey,
  ReactComponentType
>;
