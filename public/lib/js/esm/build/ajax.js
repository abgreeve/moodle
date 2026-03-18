import{loadAmdModule as t}from"@moodle/lms/core/amd";var e,s=()=>(e||(e=t("core/ajax")),e),i=async(o,n,a,r,u,l)=>(await s()).call(o,n,a,r,u,l),d={call:i},x=d;export{i as call,x as default};
/**
 * ESM shim for the legacy AMD ajax API.
 *
 * @module     core/ajax
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
