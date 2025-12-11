var d=Object.create;var E=Object.defineProperty;var p=Object.getOwnPropertyDescriptor;var k=Object.getOwnPropertyNames;var T=Object.getPrototypeOf,_=Object.prototype.hasOwnProperty;var i=(e,r)=>()=>(r||e((r={exports:{}}).exports,r),r.exports);var a=(e,r,t,o)=>{if(r&&typeof r=="object"||typeof r=="function")for(let s of k(r))!_.call(e,s)&&s!==t&&E(e,s,{get:()=>r[s],enumerable:!(o=p(r,s))||o.enumerable});return e};var f=(e,r,t)=>(t=e!=null?d(T(e)):{},a(r||!e||!e.__esModule?E(t,"default",{value:e,enumerable:!0}):t,e));var x=i(l=>{"use strict";var m=Symbol.for("react.transitional.element"),A=Symbol.for("react.fragment");function n(e,r,t){var o=null;if(t!==void 0&&(o=""+t),r.key!==void 0&&(o=""+r.key),"key"in r){t={};for(var s in r)s!=="key"&&(t[s]=r[s])}else t=r;return r=t.ref,{$$typeof:m,type:e,key:o,ref:r!==void 0?r:null,props:t}}l.Fragment=A;l.jsx=n;l.jsxs=n});var v=i((R,j)=>{"use strict";j.exports=x()});var u=f(v(),1);var export_Fragment=u.Fragment;var export_jsx=u.jsx;var export_jsxs=u.jsxs;export{export_Fragment as Fragment,export_jsx as jsx,export_jsxs as jsxs};
/*! Bundled license information:

react/cjs/react-jsx-runtime.production.js:
  (**
   * @license React
   * react-jsx-runtime.production.js
   *
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *)
*/
