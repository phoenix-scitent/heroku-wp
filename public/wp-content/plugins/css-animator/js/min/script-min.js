"undefined"==typeof Modernizr&&(window.Modernizr=function(n,t,e){function o(n){p.cssText=n}function i(n,t){return o(m.join(n+";")+(t||""))}function r(n,t){return typeof n===t}function d(n,t){return!!~(""+n).indexOf(t)}function u(n,t,o){for(var i in n){var d=t[n[i]];if(d!==e)return o===!1?n[i]:r(d,"function")?d.bind(o||t):d}return!1}var c="2.8.3",l={},a=t.documentElement,f="modernizr",s=t.createElement(f),p=s.style,h,y={}.toString,m=" -webkit- -moz- -o- -ms- ".split(" "),v={},w={},b={},g=[],z=g.slice,C,j=function(n,e,o,i){var r,d,u,c,l=t.createElement("div"),s=t.body,p=s||t.createElement("body");if(parseInt(o,10))for(;o--;)u=t.createElement("div"),u.id=i?i[o]:f+(o+1),l.appendChild(u);return r=["&#173;",'<style id="s',f,'">',n,"</style>"].join(""),l.id=f,(s?l:p).innerHTML+=r,p.appendChild(l),s||(p.style.background="",p.style.overflow="hidden",c=a.style.overflow,a.style.overflow="hidden",a.appendChild(p)),d=e(l,n),s?l.parentNode.removeChild(l):(p.parentNode.removeChild(p),a.style.overflow=c),!!d},T={}.hasOwnProperty,E;E=r(T,"undefined")||r(T.call,"undefined")?function(n,t){return t in n&&r(n.constructor.prototype[t],"undefined")}:function(n,t){return T.call(n,t)},Function.prototype.bind||(Function.prototype.bind=function(n){var t=this;if("function"!=typeof t)throw new TypeError;var e=z.call(arguments,1),o=function(){if(this instanceof o){var i=function(){};i.prototype=t.prototype;var r=new i,d=t.apply(r,e.concat(z.call(arguments)));return Object(d)===d?d:r}return t.apply(n,e.concat(z.call(arguments)))};return o}),v.touch=function(){var e;return"ontouchstart"in n||n.DocumentTouch&&t instanceof DocumentTouch?e=!0:j(["@media (",m.join("touch-enabled),("),f,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(n){e=9===n.offsetTop}),e};for(var x in v)E(v,x)&&(C=x.toLowerCase(),l[C]=v[x](),g.push((l[C]?"":"no-")+C));return l.addTest=function(n,t){if("object"==typeof n)for(var o in n)E(n,o)&&l.addTest(o,n[o]);else{if(n=n.toLowerCase(),l[n]!==e)return l;t="function"==typeof t?t():t,"undefined"!=typeof enableClasses&&enableClasses&&(a.className+=" "+(t?"":"no-")+n),l[n]=t}return l},o(""),s=h=null,l._version=c,l._prefixes=m,l.testStyles=j,l}(this,this.document)),jQuery(document).ready(function($){"use strict";function n(){return Modernizr.touch&&jQuery(window).width()<=1e3||window.screen.width<=1281&&window.devicePixelRatio>1}var t=".unfold-3d-to-left, .unfold-3d-to-right, .unfold-3d-to-top, .unfold-3d-to-bottom, .unfold-3d-horizontal, .unfold-3d-vertical",e=t.replace(/[.,]/g,"");$(t).each(function(){$(this).find(".unfolder-content").width($(this).width())}),$(window).resize(function(){var n=".unfold-3d-to-left, .unfold-3d-to-right, .unfold-3d-to-top, .unfold-3d-to-bottom, .unfold-3d-horizontal, .unfold-3d-vertical",t=n.replace(/[.,]/g,"");$(n).each(function(){$(this).find(".unfolder-content").width($(this).width())})}),n()&&$(".gambit-css-animation[data-enable_animator=nomobile]").each(function(){$(this).removeAttr("style"),$(this).removeAttr("class")})});