import b,{useState as m}from"react";function f(s){let{label:t,type:a="button",disabled:u=!1,loading:e=!1,onClick:n,className:i="",...c}=s,o=u||e,r=c,[B,d]=m(0);return b.createElement("button",{type:a,disabled:o,onClick:l=>{if(o){l.preventDefault();return}d(p=>p+1),n&&n(l)},className:i,...r},e?t?`${t}\u2026`:"Loading\u2026":t)}export{f as default};
//# sourceMappingURL=Button.js.map
