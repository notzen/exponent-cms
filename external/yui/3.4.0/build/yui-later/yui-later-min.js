/*
YUI 3.4.0 (build 3928)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("yui-later",function(b){var a=[];b.later=function(j,f,k,g,h){j=j||0;g=(!b.Lang.isUndefined(g))?b.Array(g):g;var i=false,c=(f&&b.Lang.isString(k))?f[k]:k,d=function(){if(!i){if(!c.apply){c(g[0],g[1],g[2],g[3]);}else{c.apply(f,g||a);}}},e=(h)?setInterval(d,j):setTimeout(d,j);return{id:e,interval:h,cancel:function(){i=true;if(this.interval){clearInterval(e);}else{clearTimeout(e);}}};};b.Lang.later=b.later;},"3.4.0",{requires:["yui-base"]});