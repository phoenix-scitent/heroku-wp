"undefined"==typeof Modernizr&&(window.Modernizr=function(n,t,e){function o(n){p.cssText=n}function i(n,t){return o(y.join(n+";")+(t||""))}function r(n,t){return typeof n===t}function d(n,t){return!!~(""+n).indexOf(t)}function u(n,t,o){for(var i in n){var d=t[n[i]];if(d!==e)return o===!1?n[i]:r(d,"function")?d.bind(o||t):d}return!1}var a="2.8.3",f={},c=t.documentElement,l="modernizr",s=t.createElement(l),p=s.style,h,m={}.toString,y=" -webkit- -moz- -o- -ms- ".split(" "),w={},v={},b={},g=[],C=g.slice,j,z=function(n,e,o,i){var r,d,u,a,f=t.createElement("div"),s=t.body,p=s||t.createElement("body");if(parseInt(o,10))for(;o--;)u=t.createElement("div"),u.id=i?i[o]:l+(o+1),f.appendChild(u);return r=["&#173;",'<style id="s',l,'">',n,"</style>"].join(""),f.id=l,(s?f:p).innerHTML+=r,p.appendChild(f),s||(p.style.background="",p.style.overflow="hidden",a=c.style.overflow,c.style.overflow="hidden",c.appendChild(p)),d=e(f,n),s?f.parentNode.removeChild(f):(p.parentNode.removeChild(p),c.style.overflow=a),!!d},_={}.hasOwnProperty,T;T=r(_,"undefined")||r(_.call,"undefined")?function(n,t){return t in n&&r(n.constructor.prototype[t],"undefined")}:function(n,t){return _.call(n,t)},Function.prototype.bind||(Function.prototype.bind=function(n){var t=this;if("function"!=typeof t)throw new TypeError;var e=C.call(arguments,1),o=function(){if(this instanceof o){var i=function(){};i.prototype=t.prototype;var r=new i,d=t.apply(r,e.concat(C.call(arguments)));return Object(d)===d?d:r}return t.apply(n,e.concat(C.call(arguments)))};return o}),w.touch=function(){var e;return"ontouchstart"in n||n.DocumentTouch&&t instanceof DocumentTouch?e=!0:z(["@media (",y.join("touch-enabled),("),l,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(n){e=9===n.offsetTop}),e};for(var E in w)T(w,E)&&(j=E.toLowerCase(),f[j]=w[E](),g.push((f[j]?"":"no-")+j));return f.addTest=function(n,t){if("object"==typeof n)for(var o in n)T(n,o)&&f.addTest(o,n[o]);else{if(n=n.toLowerCase(),f[n]!==e)return f;t="function"==typeof t?t():t,"undefined"!=typeof enableClasses&&enableClasses&&(c.className+=" "+(t?"":"no-")+n),f[n]=t}return f},o(""),s=h=null,f._version=a,f._prefixes=y,f.testStyles=z,f}(this,this.document)),jQuery(document).ready(function($){"use strict";function n(){return Modernizr.touch&&jQuery(window).width()<=1e3||window.screen.width<=1281&&window.devicePixelRatio>1}var t=".unfold-3d-to-left, .unfold-3d-to-right, .unfold-3d-to-top, .unfold-3d-to-bottom, .unfold-3d-horizontal, .unfold-3d-vertical",e=t.replace(/[.,]/g,"");$(t).each(function(){$(this).find(".unfolder-content").width($(this).width())}),$(window).resize(function(){var n=".unfold-3d-to-left, .unfold-3d-to-right, .unfold-3d-to-top, .unfold-3d-to-bottom, .unfold-3d-horizontal, .unfold-3d-vertical",t=n.replace(/[.,]/g,"");$(n).each(function(){$(this).find(".unfolder-content").width($(this).width())})}),n()&&$(".gambit-css-animation[data-enable_animator=nomobile]").each(function(){$(this).removeAttr("style"),$(this).removeAttr("class")})}),jQuery(document).ready(function($){"undefined"!=typeof $.fn.waypoint&&$(".gambit-css-animation.wpb_animate_when_almost_visible").waypoint(function(){$(this).addClass("wpb_start_animation")},{offset:"90%",triggerOnce:!0})});