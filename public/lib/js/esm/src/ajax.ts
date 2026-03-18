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
 * ESM shim for the legacy AMD ajax API.
 *
 * @module     core/ajax
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {loadAmdModule} from "@moodle/lms/core/amd";

type AjaxRequest = {
    methodname: string;
    args: Record<string, unknown>;
    done?: (...args: unknown[]) => void;
    fail?: (...args: unknown[]) => void;
};

type AjaxModule = {
    call: (
        requests: AjaxRequest[],
        async?: boolean,
        loginrequired?: boolean,
        nosessionupdate?: boolean,
        timeout?: number,
        cachekey?: number
    ) => PromiseLike<unknown>[];
};

let ajaxModulePromise: Promise<AjaxModule> | undefined;

/**
 * Lazily load the legacy AMD `core/ajax` module once.
 *
 * @returns The resolved AMD module.
 */
const getAjaxModule = (): Promise<AjaxModule> => {
    if (!ajaxModulePromise) {
        ajaxModulePromise = loadAmdModule<AjaxModule>("core/ajax");
    }

    return ajaxModulePromise;
};

/**
 * Make a series of ajax requests and return all the responses.
 *
 * @param requests Array of requests with each containing methodname and args properties.
 * @param async If false this function will not return until the promises are resolved.
 * @param loginrequired When false this function calls an endpoint which does not use the session.
 * @param nosessionupdate If true, the timemodified for the session will not be updated.
 * @param timeout Number of milliseconds to wait for a response.
 * @param cachekey A cache key used to improve browser-side caching.
 * @returns The promises for each of the supplied requests.
 */
export const call = async(
    requests: AjaxRequest[],
    async?: boolean,
    loginrequired?: boolean,
    nosessionupdate?: boolean,
    timeout?: number,
    cachekey?: number
): PromiseLike<unknown>[] => {
    return (await getAjaxModule()).call(
        requests,
        async,
        loginrequired,
        nosessionupdate,
        timeout,
        cachekey
    );
};

const Ajax = {
    call,
};

export default Ajax;
