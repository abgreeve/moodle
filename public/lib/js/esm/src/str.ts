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
 * ESM shim for the legacy AMD string API.
 * NOTE: Temporary bridge until core/str is available as ESM.
 * Do not add Legacy APIs here.
 *
 * @module     core/str
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {loadAmdModule} from "@moodle/lms/core/amd";

type StringRequest = {
    key: string;
    component?: string;
    lang?: string;
    param?: object | string;
};

type StrModule = {
    getString: (
        key: string,
        component?: string,
        param?: object | string,
        lang?: string
    ) => Promise<string>;
    getStrings: (requests: StringRequest[]) => Promise<string[]>;
};

let strModulePromise: Promise<StrModule> | undefined;

/**
 * Lazily load the legacy AMD `core/str` module once.
 *
 * @returns The resolved AMD module.
 */
const getStrModule = (): Promise<StrModule> => {
    if (!strModulePromise) {
        strModulePromise = loadAmdModule<StrModule>("core/str");
    }

    return strModulePromise;
};

/**
 * Return a Promise that resolves to a string.
 *
 * @param key The language string key.
 * @param component The language string component.
 * @param param The param for variable expansion in the string.
 * @param lang The users language.
 * @returns A native Promise containing the translated string.
 */
export const getString = async(
    key: string,
    component?: string,
    param?: object | string,
    lang?: string
): Promise<string> => {
    return (await getStrModule()).getString(key, component, param, lang);
};

/**
 * Make a batch request to load a set of strings.
 *
 * @param requests List of strings to fetch.
 * @returns A native promise containing an array of the translated strings.
 */
export const getStrings = async(requests: StringRequest[]): Promise<string[]> => {
    return (await getStrModule()).getStrings(requests);
};
