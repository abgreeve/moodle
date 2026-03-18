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
 * Shared helpers for loading legacy AMD modules from ESM entrypoints.
 *
 * @module     core/amd
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export type AmdLoader = (
    dependencies: string[],
    onLoad: (module: unknown) => void,
    onError?: (error: Error) => void
) => void;

/**
 * Resolve the browser AMD loader.
 *
 * @returns The global AMD loader function.
 */
export const getAmdLoader = (): AmdLoader => {
    const loader = (globalThis as {require?: AmdLoader}).require;

    if (typeof loader !== "function") {
        throw new Error("The Moodle AMD loader is not available.");
    }

    return loader;
};

/**
 * Load a single AMD module through the Moodle browser loader.
 *
 * @param moduleName The AMD module name to resolve.
 * @returns The resolved AMD module.
 */
export const loadAmdModule = <T>(moduleName: string): Promise<T> => {
    return new Promise((resolve, reject) => {
        try {
            getAmdLoader()([moduleName], (module) => resolve(module as T), reject);
        } catch (error) {
            reject(error);
        }
    });
};
