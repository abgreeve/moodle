import{loadAmdModule as g}from"@moodle/lms/core/amd";var r,e=()=>(r||(r=g("core/str")),r),u=async(t,n,s,i)=>(await e()).getString(t,n,s,i),m=async t=>(await e()).getStrings(t);export{u as getString,m as getStrings};
/**
 * ESM shim for the legacy AMD string API.
 * NOTE: Temporary bridge until core/str is available as ESM.
 * Do not add Legacy APIs here.
 *
 * @module     core/str
 * @copyright  2026 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
