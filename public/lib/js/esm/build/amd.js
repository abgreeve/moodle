var n=()=>{let r=globalThis.require;if(typeof r!="function")throw new Error("The Moodle AMD loader is not available.");return r},t=r=>new Promise((d,e)=>{try{n()([r],o=>d(o),e)}catch(o){e(o)}});export{n as getAmdLoader,t as loadAmdModule};
/**
 * Shared helpers for loading legacy AMD modules from ESM entrypoints.
 *
 * @module     core/amd
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
