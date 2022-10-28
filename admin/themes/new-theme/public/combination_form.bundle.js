(()=>{var e={4431:function(e,t,i){var r;!function(n){"use strict";var o,s=/^-?(?:\d+(?:\.\d*)?|\.\d+)(?:e[+-]?\d+)?$/i,a=Math.ceil,c=Math.floor,p="[BigNumber Error] ",l=p+"Number primitive has more than 15 significant digits: ",u=1e14,d=14,h=9007199254740991,m=[1,10,100,1e3,1e4,1e5,1e6,1e7,1e8,1e9,1e10,1e11,1e12,1e13],f=1e7,g=1e9;function b(e){var t=0|e;return e>0||e===t?t:t-1}function w(e){for(var t,i,r=1,n=e.length,o=e[0]+"";r<n;){for(t=e[r++]+"",i=d-t.length;i--;t="0"+t);o+=t}for(n=o.length;48===o.charCodeAt(--n););return o.slice(0,n+1||1)}function y(e,t){var i,r,n=e.c,o=t.c,s=e.s,a=t.s,c=e.e,p=t.e;if(!s||!a)return null;if(i=n&&!n[0],r=o&&!o[0],i||r)return i?r?0:-a:s;if(s!=a)return s;if(i=s<0,r=c==p,!n||!o)return r?0:!n^i?1:-1;if(!r)return c>p^i?1:-1;for(a=(c=n.length)<(p=o.length)?c:p,s=0;s<a;s++)if(n[s]!=o[s])return n[s]>o[s]^i?1:-1;return c==p?0:c>p^i?1:-1}function S(e,t,i,r){if(e<t||e>i||e!==c(e))throw Error(p+(r||"Argument")+("number"==typeof e?e<t||e>i?" out of range: ":" not an integer: ":" not a primitive number: ")+String(e))}function x(e){var t=e.c.length-1;return b(e.e/d)==t&&e.c[t]%2!=0}function v(e,t){return(e.length>1?e.charAt(0)+"."+e.slice(1):e)+(t<0?"e":"e+")+t}function _(e,t,i){var r,n;if(t<0){for(n=i+".";++t;n+=i);e=n+e}else if(++t>(r=e.length)){for(n=i,t-=r;--t;n+=i);e+=n}else t<r&&(e=e.slice(0,t)+"."+e.slice(t));return e}o=function e(t){var i,r,n,o,I,P,E,N,T,O,C=z.prototype={constructor:z,toString:null,valueOf:null},F=new z(1),M=20,D=4,R=-7,k=21,B=-1e7,A=1e7,$=!1,j=1,G=0,Z={prefix:"",groupSize:3,secondaryGroupSize:0,groupSeparator:",",decimalSeparator:".",fractionGroupSize:0,fractionGroupSeparator:" ",suffix:""},U="0123456789abcdefghijklmnopqrstuvwxyz";function z(e,t){var i,o,a,p,u,m,f,g,b=this;if(!(b instanceof z))return new z(e,t);if(null==t){if(e&&!0===e._isBigNumber)return b.s=e.s,void(!e.c||e.e>A?b.c=b.e=null:e.e<B?b.c=[b.e=0]:(b.e=e.e,b.c=e.c.slice()));if((m="number"==typeof e)&&0*e==0){if(b.s=1/e<0?(e=-e,-1):1,e===~~e){for(p=0,u=e;u>=10;u/=10,p++);return void(p>A?b.c=b.e=null:(b.e=p,b.c=[e]))}g=String(e)}else{if(!s.test(g=String(e)))return n(b,g,m);b.s=45==g.charCodeAt(0)?(g=g.slice(1),-1):1}(p=g.indexOf("."))>-1&&(g=g.replace(".","")),(u=g.search(/e/i))>0?(p<0&&(p=u),p+=+g.slice(u+1),g=g.substring(0,u)):p<0&&(p=g.length)}else{if(S(t,2,U.length,"Base"),10==t)return K(b=new z(e),M+b.e+1,D);if(g=String(e),m="number"==typeof e){if(0*e!=0)return n(b,g,m,t);if(b.s=1/e<0?(g=g.slice(1),-1):1,z.DEBUG&&g.replace(/^0\.0*|\./,"").length>15)throw Error(l+e)}else b.s=45===g.charCodeAt(0)?(g=g.slice(1),-1):1;for(i=U.slice(0,t),p=u=0,f=g.length;u<f;u++)if(i.indexOf(o=g.charAt(u))<0){if("."==o){if(u>p){p=f;continue}}else if(!a&&(g==g.toUpperCase()&&(g=g.toLowerCase())||g==g.toLowerCase()&&(g=g.toUpperCase()))){a=!0,u=-1,p=0;continue}return n(b,String(e),m,t)}m=!1,(p=(g=r(g,t,10,b.s)).indexOf("."))>-1?g=g.replace(".",""):p=g.length}for(u=0;48===g.charCodeAt(u);u++);for(f=g.length;48===g.charCodeAt(--f););if(g=g.slice(u,++f)){if(f-=u,m&&z.DEBUG&&f>15&&(e>h||e!==c(e)))throw Error(l+b.s*e);if((p=p-u-1)>A)b.c=b.e=null;else if(p<B)b.c=[b.e=0];else{if(b.e=p,b.c=[],u=(p+1)%d,p<0&&(u+=d),u<f){for(u&&b.c.push(+g.slice(0,u)),f-=d;u<f;)b.c.push(+g.slice(u,u+=d));u=d-(g=g.slice(u)).length}else u-=f;for(;u--;g+="0");b.c.push(+g)}}else b.c=[b.e=0]}function L(e,t,i,r){var n,o,s,a,c;if(null==i?i=D:S(i,0,8),!e.c)return e.toString();if(n=e.c[0],s=e.e,null==t)c=w(e.c),c=1==r||2==r&&(s<=R||s>=k)?v(c,s):_(c,s,"0");else if(o=(e=K(new z(e),t,i)).e,a=(c=w(e.c)).length,1==r||2==r&&(t<=o||o<=R)){for(;a<t;c+="0",a++);c=v(c,o)}else if(t-=s,c=_(c,o,"0"),o+1>a){if(--t>0)for(c+=".";t--;c+="0");}else if((t+=o-a)>0)for(o+1==a&&(c+=".");t--;c+="0");return e.s<0&&n?"-"+c:c}function q(e,t){for(var i,r=1,n=new z(e[0]);r<e.length;r++){if(!(i=new z(e[r])).s){n=i;break}t.call(n,i)&&(n=i)}return n}function V(e,t,i){for(var r=1,n=t.length;!t[--n];t.pop());for(n=t[0];n>=10;n/=10,r++);return(i=r+i*d-1)>A?e.c=e.e=null:i<B?e.c=[e.e=0]:(e.e=i,e.c=t),e}function K(e,t,i,r){var n,o,s,p,l,h,f,g=e.c,b=m;if(g){e:{for(n=1,p=g[0];p>=10;p/=10,n++);if((o=t-n)<0)o+=d,s=t,f=(l=g[h=0])/b[n-s-1]%10|0;else if((h=a((o+1)/d))>=g.length){if(!r)break e;for(;g.length<=h;g.push(0));l=f=0,n=1,s=(o%=d)-d+1}else{for(l=p=g[h],n=1;p>=10;p/=10,n++);f=(s=(o%=d)-d+n)<0?0:l/b[n-s-1]%10|0}if(r=r||t<0||null!=g[h+1]||(s<0?l:l%b[n-s-1]),r=i<4?(f||r)&&(0==i||i==(e.s<0?3:2)):f>5||5==f&&(4==i||r||6==i&&(o>0?s>0?l/b[n-s]:0:g[h-1])%10&1||i==(e.s<0?8:7)),t<1||!g[0])return g.length=0,r?(t-=e.e+1,g[0]=b[(d-t%d)%d],e.e=-t||0):g[0]=e.e=0,e;if(0==o?(g.length=h,p=1,h--):(g.length=h+1,p=b[d-o],g[h]=s>0?c(l/b[n-s]%b[s])*p:0),r)for(;;){if(0==h){for(o=1,s=g[0];s>=10;s/=10,o++);for(s=g[0]+=p,p=1;s>=10;s/=10,p++);o!=p&&(e.e++,g[0]==u&&(g[0]=1));break}if(g[h]+=p,g[h]!=u)break;g[h--]=0,p=1}for(o=g.length;0===g[--o];g.pop());}e.e>A?e.c=e.e=null:e.e<B&&(e.c=[e.e=0])}return e}function W(e){var t,i=e.e;return null===i?e.toString():(t=w(e.c),t=i<=R||i>=k?v(t,i):_(t,i,"0"),e.s<0?"-"+t:t)}return z.clone=e,z.ROUND_UP=0,z.ROUND_DOWN=1,z.ROUND_CEIL=2,z.ROUND_FLOOR=3,z.ROUND_HALF_UP=4,z.ROUND_HALF_DOWN=5,z.ROUND_HALF_EVEN=6,z.ROUND_HALF_CEIL=7,z.ROUND_HALF_FLOOR=8,z.EUCLID=9,z.config=z.set=function(e){var t,i;if(null!=e){if("object"!=typeof e)throw Error(p+"Object expected: "+e);if(e.hasOwnProperty(t="DECIMAL_PLACES")&&(S(i=e[t],0,g,t),M=i),e.hasOwnProperty(t="ROUNDING_MODE")&&(S(i=e[t],0,8,t),D=i),e.hasOwnProperty(t="EXPONENTIAL_AT")&&((i=e[t])&&i.pop?(S(i[0],-g,0,t),S(i[1],0,g,t),R=i[0],k=i[1]):(S(i,-g,g,t),R=-(k=i<0?-i:i))),e.hasOwnProperty(t="RANGE"))if((i=e[t])&&i.pop)S(i[0],-g,-1,t),S(i[1],1,g,t),B=i[0],A=i[1];else{if(S(i,-g,g,t),!i)throw Error(p+t+" cannot be zero: "+i);B=-(A=i<0?-i:i)}if(e.hasOwnProperty(t="CRYPTO")){if((i=e[t])!==!!i)throw Error(p+t+" not true or false: "+i);if(i){if("undefined"==typeof crypto||!crypto||!crypto.getRandomValues&&!crypto.randomBytes)throw $=!i,Error(p+"crypto unavailable");$=i}else $=i}if(e.hasOwnProperty(t="MODULO_MODE")&&(S(i=e[t],0,9,t),j=i),e.hasOwnProperty(t="POW_PRECISION")&&(S(i=e[t],0,g,t),G=i),e.hasOwnProperty(t="FORMAT")){if("object"!=typeof(i=e[t]))throw Error(p+t+" not an object: "+i);Z=i}if(e.hasOwnProperty(t="ALPHABET")){if("string"!=typeof(i=e[t])||/^.?$|[+\-.\s]|(.).*\1/.test(i))throw Error(p+t+" invalid: "+i);U=i}}return{DECIMAL_PLACES:M,ROUNDING_MODE:D,EXPONENTIAL_AT:[R,k],RANGE:[B,A],CRYPTO:$,MODULO_MODE:j,POW_PRECISION:G,FORMAT:Z,ALPHABET:U}},z.isBigNumber=function(e){if(!e||!0!==e._isBigNumber)return!1;if(!z.DEBUG)return!0;var t,i,r=e.c,n=e.e,o=e.s;e:if("[object Array]"=={}.toString.call(r)){if((1===o||-1===o)&&n>=-g&&n<=g&&n===c(n)){if(0===r[0]){if(0===n&&1===r.length)return!0;break e}if((t=(n+1)%d)<1&&(t+=d),String(r[0]).length==t){for(t=0;t<r.length;t++)if((i=r[t])<0||i>=u||i!==c(i))break e;if(0!==i)return!0}}}else if(null===r&&null===n&&(null===o||1===o||-1===o))return!0;throw Error(p+"Invalid BigNumber: "+e)},z.maximum=z.max=function(){return q(arguments,C.lt)},z.minimum=z.min=function(){return q(arguments,C.gt)},z.random=(o=9007199254740992,I=Math.random()*o&2097151?function(){return c(Math.random()*o)}:function(){return 8388608*(1073741824*Math.random()|0)+(8388608*Math.random()|0)},function(e){var t,i,r,n,o,s=0,l=[],u=new z(F);if(null==e?e=M:S(e,0,g),n=a(e/d),$)if(crypto.getRandomValues){for(t=crypto.getRandomValues(new Uint32Array(n*=2));s<n;)(o=131072*t[s]+(t[s+1]>>>11))>=9e15?(i=crypto.getRandomValues(new Uint32Array(2)),t[s]=i[0],t[s+1]=i[1]):(l.push(o%1e14),s+=2);s=n/2}else{if(!crypto.randomBytes)throw $=!1,Error(p+"crypto unavailable");for(t=crypto.randomBytes(n*=7);s<n;)(o=281474976710656*(31&t[s])+1099511627776*t[s+1]+4294967296*t[s+2]+16777216*t[s+3]+(t[s+4]<<16)+(t[s+5]<<8)+t[s+6])>=9e15?crypto.randomBytes(7).copy(t,s):(l.push(o%1e14),s+=7);s=n/7}if(!$)for(;s<n;)(o=I())<9e15&&(l[s++]=o%1e14);for(n=l[--s],e%=d,n&&e&&(o=m[d-e],l[s]=c(n/o)*o);0===l[s];l.pop(),s--);if(s<0)l=[r=0];else{for(r=-1;0===l[0];l.splice(0,1),r-=d);for(s=1,o=l[0];o>=10;o/=10,s++);s<d&&(r-=d-s)}return u.e=r,u.c=l,u}),z.sum=function(){for(var e=1,t=arguments,i=new z(t[0]);e<t.length;)i=i.plus(t[e++]);return i},r=function(){var e="0123456789";function t(e,t,i,r){for(var n,o,s=[0],a=0,c=e.length;a<c;){for(o=s.length;o--;s[o]*=t);for(s[0]+=r.indexOf(e.charAt(a++)),n=0;n<s.length;n++)s[n]>i-1&&(null==s[n+1]&&(s[n+1]=0),s[n+1]+=s[n]/i|0,s[n]%=i)}return s.reverse()}return function(r,n,o,s,a){var c,p,l,u,d,h,m,f,g=r.indexOf("."),b=M,y=D;for(g>=0&&(u=G,G=0,r=r.replace(".",""),h=(f=new z(n)).pow(r.length-g),G=u,f.c=t(_(w(h.c),h.e,"0"),10,o,e),f.e=f.c.length),l=u=(m=t(r,n,o,a?(c=U,e):(c=e,U))).length;0==m[--u];m.pop());if(!m[0])return c.charAt(0);if(g<0?--l:(h.c=m,h.e=l,h.s=s,m=(h=i(h,f,b,y,o)).c,d=h.r,l=h.e),g=m[p=l+b+1],u=o/2,d=d||p<0||null!=m[p+1],d=y<4?(null!=g||d)&&(0==y||y==(h.s<0?3:2)):g>u||g==u&&(4==y||d||6==y&&1&m[p-1]||y==(h.s<0?8:7)),p<1||!m[0])r=d?_(c.charAt(1),-b,c.charAt(0)):c.charAt(0);else{if(m.length=p,d)for(--o;++m[--p]>o;)m[p]=0,p||(++l,m=[1].concat(m));for(u=m.length;!m[--u];);for(g=0,r="";g<=u;r+=c.charAt(m[g++]));r=_(r,l,c.charAt(0))}return r}}(),i=function(){function e(e,t,i){var r,n,o,s,a=0,c=e.length,p=t%f,l=t/f|0;for(e=e.slice();c--;)a=((n=p*(o=e[c]%f)+(r=l*o+(s=e[c]/f|0)*p)%f*f+a)/i|0)+(r/f|0)+l*s,e[c]=n%i;return a&&(e=[a].concat(e)),e}function t(e,t,i,r){var n,o;if(i!=r)o=i>r?1:-1;else for(n=o=0;n<i;n++)if(e[n]!=t[n]){o=e[n]>t[n]?1:-1;break}return o}function i(e,t,i,r){for(var n=0;i--;)e[i]-=n,n=e[i]<t[i]?1:0,e[i]=n*r+e[i]-t[i];for(;!e[0]&&e.length>1;e.splice(0,1));}return function(r,n,o,s,a){var p,l,h,m,f,g,w,y,S,x,v,_,I,P,E,N,T,O=r.s==n.s?1:-1,C=r.c,F=n.c;if(!(C&&C[0]&&F&&F[0]))return new z(r.s&&n.s&&(C?!F||C[0]!=F[0]:F)?C&&0==C[0]||!F?0*O:O/0:NaN);for(S=(y=new z(O)).c=[],O=o+(l=r.e-n.e)+1,a||(a=u,l=b(r.e/d)-b(n.e/d),O=O/d|0),h=0;F[h]==(C[h]||0);h++);if(F[h]>(C[h]||0)&&l--,O<0)S.push(1),m=!0;else{for(P=C.length,N=F.length,h=0,O+=2,(f=c(a/(F[0]+1)))>1&&(F=e(F,f,a),C=e(C,f,a),N=F.length,P=C.length),I=N,v=(x=C.slice(0,N)).length;v<N;x[v++]=0);T=F.slice(),T=[0].concat(T),E=F[0],F[1]>=a/2&&E++;do{if(f=0,(p=t(F,x,N,v))<0){if(_=x[0],N!=v&&(_=_*a+(x[1]||0)),(f=c(_/E))>1)for(f>=a&&(f=a-1),w=(g=e(F,f,a)).length,v=x.length;1==t(g,x,w,v);)f--,i(g,N<w?T:F,w,a),w=g.length,p=1;else 0==f&&(p=f=1),w=(g=F.slice()).length;if(w<v&&(g=[0].concat(g)),i(x,g,v,a),v=x.length,-1==p)for(;t(F,x,N,v)<1;)f++,i(x,N<v?T:F,v,a),v=x.length}else 0===p&&(f++,x=[0]);S[h++]=f,x[0]?x[v++]=C[I]||0:(x=[C[I]],v=1)}while((I++<P||null!=x[0])&&O--);m=null!=x[0],S[0]||S.splice(0,1)}if(a==u){for(h=1,O=S[0];O>=10;O/=10,h++);K(y,o+(y.e=h+l*d-1)+1,s,m)}else y.e=l,y.r=+m;return y}}(),P=/^(-?)0([xbo])(?=\w[\w.]*$)/i,E=/^([^.]+)\.$/,N=/^\.([^.]+)$/,T=/^-?(Infinity|NaN)$/,O=/^\s*\+(?=[\w.])|^\s+|\s+$/g,n=function(e,t,i,r){var n,o=i?t:t.replace(O,"");if(T.test(o))e.s=isNaN(o)?null:o<0?-1:1;else{if(!i&&(o=o.replace(P,(function(e,t,i){return n="x"==(i=i.toLowerCase())?16:"b"==i?2:8,r&&r!=n?e:t})),r&&(n=r,o=o.replace(E,"$1").replace(N,"0.$1")),t!=o))return new z(o,n);if(z.DEBUG)throw Error(p+"Not a"+(r?" base "+r:"")+" number: "+t);e.s=null}e.c=e.e=null},C.absoluteValue=C.abs=function(){var e=new z(this);return e.s<0&&(e.s=1),e},C.comparedTo=function(e,t){return y(this,new z(e,t))},C.decimalPlaces=C.dp=function(e,t){var i,r,n,o=this;if(null!=e)return S(e,0,g),null==t?t=D:S(t,0,8),K(new z(o),e+o.e+1,t);if(!(i=o.c))return null;if(r=((n=i.length-1)-b(this.e/d))*d,n=i[n])for(;n%10==0;n/=10,r--);return r<0&&(r=0),r},C.dividedBy=C.div=function(e,t){return i(this,new z(e,t),M,D)},C.dividedToIntegerBy=C.idiv=function(e,t){return i(this,new z(e,t),0,1)},C.exponentiatedBy=C.pow=function(e,t){var i,r,n,o,s,l,u,h,m=this;if((e=new z(e)).c&&!e.isInteger())throw Error(p+"Exponent not an integer: "+W(e));if(null!=t&&(t=new z(t)),s=e.e>14,!m.c||!m.c[0]||1==m.c[0]&&!m.e&&1==m.c.length||!e.c||!e.c[0])return h=new z(Math.pow(+W(m),s?2-x(e):+W(e))),t?h.mod(t):h;if(l=e.s<0,t){if(t.c?!t.c[0]:!t.s)return new z(NaN);(r=!l&&m.isInteger()&&t.isInteger())&&(m=m.mod(t))}else{if(e.e>9&&(m.e>0||m.e<-1||(0==m.e?m.c[0]>1||s&&m.c[1]>=24e7:m.c[0]<8e13||s&&m.c[0]<=9999975e7)))return o=m.s<0&&x(e)?-0:0,m.e>-1&&(o=1/o),new z(l?1/o:o);G&&(o=a(G/d+2))}for(s?(i=new z(.5),l&&(e.s=1),u=x(e)):u=(n=Math.abs(+W(e)))%2,h=new z(F);;){if(u){if(!(h=h.times(m)).c)break;o?h.c.length>o&&(h.c.length=o):r&&(h=h.mod(t))}if(n){if(0===(n=c(n/2)))break;u=n%2}else if(K(e=e.times(i),e.e+1,1),e.e>14)u=x(e);else{if(0===(n=+W(e)))break;u=n%2}m=m.times(m),o?m.c&&m.c.length>o&&(m.c.length=o):r&&(m=m.mod(t))}return r?h:(l&&(h=F.div(h)),t?h.mod(t):o?K(h,G,D,undefined):h)},C.integerValue=function(e){var t=new z(this);return null==e?e=D:S(e,0,8),K(t,t.e+1,e)},C.isEqualTo=C.eq=function(e,t){return 0===y(this,new z(e,t))},C.isFinite=function(){return!!this.c},C.isGreaterThan=C.gt=function(e,t){return y(this,new z(e,t))>0},C.isGreaterThanOrEqualTo=C.gte=function(e,t){return 1===(t=y(this,new z(e,t)))||0===t},C.isInteger=function(){return!!this.c&&b(this.e/d)>this.c.length-2},C.isLessThan=C.lt=function(e,t){return y(this,new z(e,t))<0},C.isLessThanOrEqualTo=C.lte=function(e,t){return-1===(t=y(this,new z(e,t)))||0===t},C.isNaN=function(){return!this.s},C.isNegative=function(){return this.s<0},C.isPositive=function(){return this.s>0},C.isZero=function(){return!!this.c&&0==this.c[0]},C.minus=function(e,t){var i,r,n,o,s=this,a=s.s;if(t=(e=new z(e,t)).s,!a||!t)return new z(NaN);if(a!=t)return e.s=-t,s.plus(e);var c=s.e/d,p=e.e/d,l=s.c,h=e.c;if(!c||!p){if(!l||!h)return l?(e.s=-t,e):new z(h?s:NaN);if(!l[0]||!h[0])return h[0]?(e.s=-t,e):new z(l[0]?s:3==D?-0:0)}if(c=b(c),p=b(p),l=l.slice(),a=c-p){for((o=a<0)?(a=-a,n=l):(p=c,n=h),n.reverse(),t=a;t--;n.push(0));n.reverse()}else for(r=(o=(a=l.length)<(t=h.length))?a:t,a=t=0;t<r;t++)if(l[t]!=h[t]){o=l[t]<h[t];break}if(o&&(n=l,l=h,h=n,e.s=-e.s),(t=(r=h.length)-(i=l.length))>0)for(;t--;l[i++]=0);for(t=u-1;r>a;){if(l[--r]<h[r]){for(i=r;i&&!l[--i];l[i]=t);--l[i],l[r]+=u}l[r]-=h[r]}for(;0==l[0];l.splice(0,1),--p);return l[0]?V(e,l,p):(e.s=3==D?-1:1,e.c=[e.e=0],e)},C.modulo=C.mod=function(e,t){var r,n,o=this;return e=new z(e,t),!o.c||!e.s||e.c&&!e.c[0]?new z(NaN):!e.c||o.c&&!o.c[0]?new z(o):(9==j?(n=e.s,e.s=1,r=i(o,e,0,3),e.s=n,r.s*=n):r=i(o,e,0,j),(e=o.minus(r.times(e))).c[0]||1!=j||(e.s=o.s),e)},C.multipliedBy=C.times=function(e,t){var i,r,n,o,s,a,c,p,l,h,m,g,w,y,S,x=this,v=x.c,_=(e=new z(e,t)).c;if(!(v&&_&&v[0]&&_[0]))return!x.s||!e.s||v&&!v[0]&&!_||_&&!_[0]&&!v?e.c=e.e=e.s=null:(e.s*=x.s,v&&_?(e.c=[0],e.e=0):e.c=e.e=null),e;for(r=b(x.e/d)+b(e.e/d),e.s*=x.s,(c=v.length)<(h=_.length)&&(w=v,v=_,_=w,n=c,c=h,h=n),n=c+h,w=[];n--;w.push(0));for(y=u,S=f,n=h;--n>=0;){for(i=0,m=_[n]%S,g=_[n]/S|0,o=n+(s=c);o>n;)i=((p=m*(p=v[--s]%S)+(a=g*p+(l=v[s]/S|0)*m)%S*S+w[o]+i)/y|0)+(a/S|0)+g*l,w[o--]=p%y;w[o]=i}return i?++r:w.splice(0,1),V(e,w,r)},C.negated=function(){var e=new z(this);return e.s=-e.s||null,e},C.plus=function(e,t){var i,r=this,n=r.s;if(t=(e=new z(e,t)).s,!n||!t)return new z(NaN);if(n!=t)return e.s=-t,r.minus(e);var o=r.e/d,s=e.e/d,a=r.c,c=e.c;if(!o||!s){if(!a||!c)return new z(n/0);if(!a[0]||!c[0])return c[0]?e:new z(a[0]?r:0*n)}if(o=b(o),s=b(s),a=a.slice(),n=o-s){for(n>0?(s=o,i=c):(n=-n,i=a),i.reverse();n--;i.push(0));i.reverse()}for((n=a.length)-(t=c.length)<0&&(i=c,c=a,a=i,t=n),n=0;t;)n=(a[--t]=a[t]+c[t]+n)/u|0,a[t]=u===a[t]?0:a[t]%u;return n&&(a=[n].concat(a),++s),V(e,a,s)},C.precision=C.sd=function(e,t){var i,r,n,o=this;if(null!=e&&e!==!!e)return S(e,1,g),null==t?t=D:S(t,0,8),K(new z(o),e,t);if(!(i=o.c))return null;if(r=(n=i.length-1)*d+1,n=i[n]){for(;n%10==0;n/=10,r--);for(n=i[0];n>=10;n/=10,r++);}return e&&o.e+1>r&&(r=o.e+1),r},C.shiftedBy=function(e){return S(e,-9007199254740991,h),this.times("1e"+e)},C.squareRoot=C.sqrt=function(){var e,t,r,n,o,s=this,a=s.c,c=s.s,p=s.e,l=M+4,u=new z("0.5");if(1!==c||!a||!a[0])return new z(!c||c<0&&(!a||a[0])?NaN:a?s:1/0);if(0==(c=Math.sqrt(+W(s)))||c==1/0?(((t=w(a)).length+p)%2==0&&(t+="0"),c=Math.sqrt(+t),p=b((p+1)/2)-(p<0||p%2),r=new z(t=c==1/0?"5e"+p:(t=c.toExponential()).slice(0,t.indexOf("e")+1)+p)):r=new z(c+""),r.c[0])for((c=(p=r.e)+l)<3&&(c=0);;)if(o=r,r=u.times(o.plus(i(s,o,l,1))),w(o.c).slice(0,c)===(t=w(r.c)).slice(0,c)){if(r.e<p&&--c,"9999"!=(t=t.slice(c-3,c+1))&&(n||"4999"!=t)){+t&&(+t.slice(1)||"5"!=t.charAt(0))||(K(r,r.e+M+2,1),e=!r.times(r).eq(s));break}if(!n&&(K(o,o.e+M+2,0),o.times(o).eq(s))){r=o;break}l+=4,c+=4,n=1}return K(r,r.e+M+1,D,e)},C.toExponential=function(e,t){return null!=e&&(S(e,0,g),e++),L(this,e,t,1)},C.toFixed=function(e,t){return null!=e&&(S(e,0,g),e=e+this.e+1),L(this,e,t)},C.toFormat=function(e,t,i){var r,n=this;if(null==i)null!=e&&t&&"object"==typeof t?(i=t,t=null):e&&"object"==typeof e?(i=e,e=t=null):i=Z;else if("object"!=typeof i)throw Error(p+"Argument not an object: "+i);if(r=n.toFixed(e,t),n.c){var o,s=r.split("."),a=+i.groupSize,c=+i.secondaryGroupSize,l=i.groupSeparator||"",u=s[0],d=s[1],h=n.s<0,m=h?u.slice(1):u,f=m.length;if(c&&(o=a,a=c,c=o,f-=o),a>0&&f>0){for(o=f%a||a,u=m.substr(0,o);o<f;o+=a)u+=l+m.substr(o,a);c>0&&(u+=l+m.slice(o)),h&&(u="-"+u)}r=d?u+(i.decimalSeparator||"")+((c=+i.fractionGroupSize)?d.replace(new RegExp("\\d{"+c+"}\\B","g"),"$&"+(i.fractionGroupSeparator||"")):d):u}return(i.prefix||"")+r+(i.suffix||"")},C.toFraction=function(e){var t,r,n,o,s,a,c,l,u,h,f,g,b=this,y=b.c;if(null!=e&&(!(c=new z(e)).isInteger()&&(c.c||1!==c.s)||c.lt(F)))throw Error(p+"Argument "+(c.isInteger()?"out of range: ":"not an integer: ")+W(c));if(!y)return new z(b);for(t=new z(F),u=r=new z(F),n=l=new z(F),g=w(y),s=t.e=g.length-b.e-1,t.c[0]=m[(a=s%d)<0?d+a:a],e=!e||c.comparedTo(t)>0?s>0?t:u:c,a=A,A=1/0,c=new z(g),l.c[0]=0;h=i(c,t,0,1),1!=(o=r.plus(h.times(n))).comparedTo(e);)r=n,n=o,u=l.plus(h.times(o=u)),l=o,t=c.minus(h.times(o=t)),c=o;return o=i(e.minus(r),n,0,1),l=l.plus(o.times(u)),r=r.plus(o.times(n)),l.s=u.s=b.s,f=i(u,n,s*=2,D).minus(b).abs().comparedTo(i(l,r,s,D).minus(b).abs())<1?[u,n]:[l,r],A=a,f},C.toNumber=function(){return+W(this)},C.toPrecision=function(e,t){return null!=e&&S(e,1,g),L(this,e,t,2)},C.toString=function(e){var t,i=this,n=i.s,o=i.e;return null===o?n?(t="Infinity",n<0&&(t="-"+t)):t="NaN":(null==e?t=o<=R||o>=k?v(w(i.c),o):_(w(i.c),o,"0"):10===e?t=_(w((i=K(new z(i),M+o+1,D)).c),i.e,"0"):(S(e,2,U.length,"Base"),t=r(_(w(i.c),o,"0"),10,e,n,!0)),n<0&&i.c[0]&&(t="-"+t)),t},C.valueOf=C.toJSON=function(){return W(this)},C._isBigNumber=!0,null!=t&&z.set(t),z}(),o.default=o.BigNumber=o,void 0===(r=function(){return o}.call(t,i,t,e))||(e.exports=r)}()},6798:(e,t,i)=>{"use strict";i.d(t,{Z:()=>r});const r=
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class{constructor(e){this.message=e,this.name="LocalizationException"}}},4902:(e,t,i)=>{"use strict";i.d(t,{NumberFormatter:()=>c});var r=i(1463),n=i(1583),o=i(3096);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
const s=i(1658);class a{constructor(e){this.numberSpecification=e}format(e,t){void 0!==t&&(this.numberSpecification=t);const i=Math.abs(e).toFixed(this.numberSpecification.getMaxFractionDigits());let[r,n]=this.extractMajorMinorDigits(i);r=this.splitMajorGroups(r),n=this.adjustMinorDigitsZeroes(n);let o=r;n&&(o+="."+n);const s=this.getCldrPattern(e<0);return o=this.addPlaceholders(o,s),o=this.replaceSymbols(o),o=this.performSpecificReplacements(o),o}extractMajorMinorDigits(e){const t=e.toString().split(".");return[t[0],void 0===t[1]?"":t[1]]}splitMajorGroups(e){if(!this.numberSpecification.isGroupingUsed())return e;const t=e.split("").reverse();let i=[];for(i.push(t.splice(0,this.numberSpecification.getPrimaryGroupSize()));t.length;)i.push(t.splice(0,this.numberSpecification.getSecondaryGroupSize()));i=i.reverse();const r=[];return i.forEach((e=>{r.push(e.reverse().join(""))})),r.join(",")}adjustMinorDigitsZeroes(e){let t=e;return t.length>this.numberSpecification.getMaxFractionDigits()&&(t=t.replace(/0+$/,"")),t.length<this.numberSpecification.getMinFractionDigits()&&(t=t.padEnd(this.numberSpecification.getMinFractionDigits(),"0")),t}getCldrPattern(e){return e?this.numberSpecification.getNegativePattern():this.numberSpecification.getPositivePattern()}replaceSymbols(e){const t=this.numberSpecification.getSymbol(),i={};return i["."]=t.getDecimal(),i[","]=t.getGroup(),i["-"]=t.getMinusSign(),i["%"]=t.getPercentSign(),i["+"]=t.getPlusSign(),this.strtr(e,i)}strtr(e,t){const i=Object.keys(t).map(s);return e.split(RegExp(`(${i.join("|")})`)).map((e=>t[e]||e)).join("")}addPlaceholders(e,t){return t.replace(/#?(,#+)*0(\.[0#]+)*/,e)}performSpecificReplacements(e){return this.numberSpecification instanceof n.Z?e.split("¤").join(this.numberSpecification.getCurrencySymbol()):e}static build(e){let t,i;return t=void 0!==e.numberSymbols?new r.Z(...e.numberSymbols):new r.Z(...e.symbol),i=e.currencySymbol?new n.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize,e.currencySymbol,e.currencyCode):new o.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize),new a(i)}}const c=a}
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */,1463:(e,t,i)=>{"use strict";i.d(t,{Z:()=>n});var r=i(6798);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */const n=class{constructor(e,t,i,r,n,o,s,a,c,p,l){this.decimal=e,this.group=t,this.list=i,this.percentSign=r,this.minusSign=n,this.plusSign=o,this.exponential=s,this.superscriptingExponent=a,this.perMille=c,this.infinity=p,this.nan=l,this.validateData()}getDecimal(){return this.decimal}getGroup(){return this.group}getList(){return this.list}getPercentSign(){return this.percentSign}getMinusSign(){return this.minusSign}getPlusSign(){return this.plusSign}getExponential(){return this.exponential}getSuperscriptingExponent(){return this.superscriptingExponent}getPerMille(){return this.perMille}getInfinity(){return this.infinity}getNan(){return this.nan}validateData(){if(!this.decimal||"string"!=typeof this.decimal)throw new r.Z("Invalid decimal");if(!this.group||"string"!=typeof this.group)throw new r.Z("Invalid group");if(!this.list||"string"!=typeof this.list)throw new r.Z("Invalid symbol list");if(!this.percentSign||"string"!=typeof this.percentSign)throw new r.Z("Invalid percentSign");if(!this.minusSign||"string"!=typeof this.minusSign)throw new r.Z("Invalid minusSign");if(!this.plusSign||"string"!=typeof this.plusSign)throw new r.Z("Invalid plusSign");if(!this.exponential||"string"!=typeof this.exponential)throw new r.Z("Invalid exponential");if(!this.superscriptingExponent||"string"!=typeof this.superscriptingExponent)throw new r.Z("Invalid superscriptingExponent");if(!this.perMille||"string"!=typeof this.perMille)throw new r.Z("Invalid perMille");if(!this.infinity||"string"!=typeof this.infinity)throw new r.Z("Invalid infinity");if(!this.nan||"string"!=typeof this.nan)throw new r.Z("Invalid nan")}}},3096:(e,t,i)=>{"use strict";i.d(t,{Z:()=>o});var r=i(6798),n=i(1463);const o=
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class{constructor(e,t,i,o,s,a,c,p){if(this.positivePattern=e,this.negativePattern=t,this.symbol=i,this.maxFractionDigits=o,this.minFractionDigits=o<s?o:s,this.groupingUsed=a,this.primaryGroupSize=c,this.secondaryGroupSize=p,!this.positivePattern||"string"!=typeof this.positivePattern)throw new r.Z("Invalid positivePattern");if(!this.negativePattern||"string"!=typeof this.negativePattern)throw new r.Z("Invalid negativePattern");if(!(this.symbol&&this.symbol instanceof n.Z))throw new r.Z("Invalid symbol");if("number"!=typeof this.maxFractionDigits)throw new r.Z("Invalid maxFractionDigits");if("number"!=typeof this.minFractionDigits)throw new r.Z("Invalid minFractionDigits");if("boolean"!=typeof this.groupingUsed)throw new r.Z("Invalid groupingUsed");if("number"!=typeof this.primaryGroupSize)throw new r.Z("Invalid primaryGroupSize");if("number"!=typeof this.secondaryGroupSize)throw new r.Z("Invalid secondaryGroupSize")}getSymbol(){return this.symbol}getPositivePattern(){return this.positivePattern}getNegativePattern(){return this.negativePattern}getMaxFractionDigits(){return this.maxFractionDigits}getMinFractionDigits(){return this.minFractionDigits}isGroupingUsed(){return this.groupingUsed}getPrimaryGroupSize(){return this.primaryGroupSize}getSecondaryGroupSize(){return this.secondaryGroupSize}}},1583:(e,t,i)=>{"use strict";i.d(t,{Z:()=>s});var r=i(6798),n=i(3096);class o extends n.Z{constructor(e,t,i,n,o,s,a,c,p,l){if(super(e,t,i,n,o,s,a,c),this.currencySymbol=p,this.currencyCode=l,!this.currencySymbol||"string"!=typeof this.currencySymbol)throw new r.Z("Invalid currencySymbol");if(!this.currencyCode||"string"!=typeof this.currencyCode)throw new r.Z("Invalid currencyCode")}static getCurrencyDisplay(){return"symbol"}getCurrencySymbol(){return this.currencySymbol}getCurrencyCode(){return this.currencyCode}}const s=o},1658:(e,t,i)=>{var r="[object Symbol]",n=/[\\^$.*+?()[\]{}|]/g,o=RegExp(n.source),s="object"==typeof i.g&&i.g&&i.g.Object===Object&&i.g,a="object"==typeof self&&self&&self.Object===Object&&self,c=s||a||Function("return this")(),p=Object.prototype.toString,l=c.Symbol,u=l?l.prototype:void 0,d=u?u.toString:void 0;function h(e){if("string"==typeof e)return e;if(function(e){return"symbol"==typeof e||function(e){return!!e&&"object"==typeof e}(e)&&p.call(e)==r}(e))return d?d.call(e):"";var t=e+"";return"0"==t&&1/e==-Infinity?"-0":t}e.exports=function(e){var t;return(e=null==(t=e)?"":h(t))&&o.test(e)?e.replace(n,"\\$&"):e}},9567:e=>{"use strict";e.exports=window.jQuery}},t={};function i(r){var n=t[r];if(void 0!==n)return n.exports;var o=t[r]={exports:{}};return e[r].call(o.exports,o,o.exports,i),o.exports}i.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return i.d(t,{a:t}),t},i.d=(e,t)=>{for(var r in t)i.o(t,r)&&!i.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},i.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),i.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var r={};(()=>{"use strict";i.r(r);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
const e="bulk-select-all-in-page",t={navigationTab:"#product_combinations-tab-nav",combinationManager:"#product_combinations_combination_manager",preloader:"#combinations-preloader",emptyState:"#combinations-empty-state",emptyFiltersState:"#combinations-empty-filters-state",combinationsPaginatedList:"#combinations-paginated-list",combinationsFormContainer:"#combinations-list-form-container",combinationsFiltersContainer:"#combinations_filters",filtersSelectorButtons:".combinations-filters-dropdown button",combinationsGeneratorContainer:"#product_combinations_generator",combinationsTable:"#combination_list",combinationsTableBody:"#combination_list tbody",combinationIdInputsSelector:".combination-id-input",deleteCombinationSelector:".delete-combination-item",combinationName:"form .combination-name-row .text-preview",paginationContainer:"#combinations-pagination",loadingSpinner:"#productCombinationsLoading",impactOnPriceInputWrapper:".combination-impact-on-price",referenceInputWrapper:".combination-reference",sortableColumns:".ps-sortable-column",combinationItemForm:{isDefaultKey:"combination_item[is_default]",deltaQuantityKey:"combination_item[delta_quantity][delta]",impactOnPriceKey:"combination_item[impact_on_price][value]",referenceKey:"combination_item[reference][value]",tokenKey:"combination_item[_token]"},editionForm:'form[name="combination_form"]',editionFormInputs:'form[name="combination_form"] input, form[name="combination_form"] textarea, form[name="combination_form"] select',editCombinationButtons:".edit-combination-item",tableRow:{isSelectedCombination:".combination-is-selected",combinationImg:".combination-image",deltaQuantityWrapper:".delta-quantity",deltaQuantityInput:e=>`#combination_list_combinations_${e}_delta_quantity_delta`,combinationCheckbox:e=>`#combination_list_combinations_${e}_is_selected`,combinationIdInput:e=>`#combination_list_combinations_${e}_combination_id`,combinationNameInput:e=>`#combination_list_combinations_${e}_name`,referenceInput:e=>`#combination_list_combinations_${e}_reference_value`,impactOnPriceInput:e=>`#combination_list_combinations_${e}_impact_on_price_value`,finalPriceTeInput:e=>`#combination_list_combinations_${e}_final_price_te`,quantityInput:e=>`#combination_list_combinations_${e}_delta_quantity_quantity`,isDefaultInput:e=>`#combination_list_combinations_${e}_is_default`,editButton:e=>`#combination_list_combinations_${e}_edit`,deleteButton:e=>`#combination_list_combinations_${e}_delete`},list:{combinationRow:".combination-list-row",priceImpactTaxExcluded:".combination-impact-on-price-tax-excluded",priceImpactTaxIncluded:".combination-impact-on-price-tax-included",isDefault:".combination-is-default-input",finalPrice:".combination-final-price",finalPricePreview:".text-preview",modifiedFieldClass:"combination-value-changed",invalidClass:"is-invalid",editionModeClass:"edition-mode",fieldInputs:".combination-list-row :input:not(.bulk-select-all):not(.combination-is-selected)",errorAlerts:".combination-list-row .alert-danger",rowActionButtons:".combination-row-actions button",footer:{cancel:"#cancel-combinations-edition",reset:"#reset-combinations-edition",save:"#save-combinations-edition"}},editModal:"#combination-edit-modal",images:{selectorContainer:".combination-images-selector",imageChoice:".combination-image-choice",checkboxContainer:".form-check",checkbox:"input[type=checkbox]"},scrollBar:".attributes-list-overflow",searchInput:"#product-combinations-generate .attributes-search",generateCombinationsButton:".generate-combinations-button",bulkCombinationFormBtn:"#combination-bulk-form-btn",bulkDeleteBtn:"#combination-bulk-delete-btn",bulkActionBtn:".bulk-action-btn",bulkActionsDropdownBtn:"#combination-bulk-actions-btn",bulkAllPreviewInput:"#bulk-all-preview",bulkSelectAll:"#bulk-select-all",bulkCheckboxesDropdownButton:"#bulk-all-selection-dropdown-button",commonBulkAllSelector:".bulk-select-all",bulkSelectAllInPage:"#bulk-select-all-in-page",bulkSelectAllInPageId:e,bulkProgressModalId:"bulk-combination-progress-modal",bulkFormModalId:"bulk-combination-form-modal",bulkForm:'form[name="bulk_combination"]',bulkDeltaQuantitySwitchName:"bulk_combination[stock][disabling_switch_delta_quantity]",bulkFixedQuantitySwitchName:"bulk_combination[stock][disabling_switch_fixed_quantity]"},{$:n}=window;class o{constructor(){this.$selectorContainer=n(t.images.selectorContainer),this.init()}init(){n(t.images.checkboxContainer,this.$selectorContainer).hide(),this.$selectorContainer.on("click",t.images.imageChoice,(e=>{if(this.$selectorContainer.hasClass("disabled"))return;const i=n(e.currentTarget),r=n(t.images.checkbox,i),o=r.prop("checked");i.toggleClass("selected",!o),r.prop("checked",!o)}))}}
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */const s="form[name=combination_form]",a={productSuppliers:"#combination_form_product_suppliers"};var c=i(4431),p=i.n(c);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
const l=/(?:(?!^-\d+))[^\d]+(?=.*[^\d])/g,u=/(?:(?!^-\d+))([^\d]+)/g,d=e=>{let t=e;const i=t.match(u);if(null===i)return t;if(i.length>1){const e=new Set(i);if(1===Array.from(e).length)return t.replace(u,"")}return t=t.replace(l,"").replace(u,"."),t};
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
const{$:h}=window;class m{constructor(e,t,i,r){this.object=e,this.modelKey=t,this.value=i,this.previousValue=r,this.propagationStopped=!1}stopPropagation(){this.propagationStopped=!0}isPropagationStopped(){return this.propagationStopped}}class f{constructor(e,t){e.length||console.error("Invalid empty form as input"),this.$form=e,this.fullModelMapping=t,this.model={},this.modelMapping={},this.modelMapping={},this.formMapping={},this.watchedProperties={},this.initFormMapping(),this.updateFullObject(),this.watchUpdates()}getModel(){return this.model}getInputsFor(e){if(!Object.prototype.hasOwnProperty.call(this.fullModelMapping,e))return;let t=this.fullModelMapping[e];Array.isArray(t)||(t=[t]);const i=[],r=this.$form.get(0);return t.forEach((e=>{const t=r.querySelectorAll(`[name="${e}"]`);t.length&&t.forEach((e=>{i.push(e)}))})),i.length?h(i):void 0}set(e,t){Object.prototype.hasOwnProperty.call(this.modelMapping,e)&&t!==this.getValue(e)&&(this.updateInputValue(e,t),this.updateObjectByKey(e,t))}watch(e,t){(Array.isArray(e)?e:[e]).forEach((e=>{Object.prototype.hasOwnProperty.call(this.watchedProperties,e)||(this.watchedProperties[e]=[]),this.watchedProperties[e].push(t)}))}getBigNumber(e){const t=this.getValue(e);return void 0===t?void 0:new(p())(d(t))}getValue(e){const t=e.split(".");return h.serializeJSON.deepGet(this.model,t)}updateFullObject(){const e=this.$form.find(":input:disabled").removeAttr("disabled"),t=this.$form.serializeArray();e.prop("disabled",!0);const i={};t.forEach((e=>{i[e.name]=e.value})),this.model={},Object.keys(this.modelMapping).forEach((e=>{const t=this.modelMapping[e],r=i[t];this.updateObjectByKey(e,r)}))}watchUpdates(){this.$form.on("change dp.change",":input",(e=>this.inputUpdated(e)))}inputUpdated(e){const t=e.currentTarget;if(!Object.prototype.hasOwnProperty.call(this.formMapping,t.name))return;const i=this.getInputValue(h(t)),r=this.formMapping[t.name];this.updateInputValue(r,i,t.name),this.updateObjectByKey(r,i)}getInputValue(e){return e.is(":checkbox")?e.is(":checked"):e.val()}updateInputValue(e,t,i){const r=this.fullModelMapping[e];Array.isArray(r)?r.forEach((e=>{i!==e&&this.updateInputByName(e,t)})):i!==r&&this.updateInputByName(r,t)}updateInputByName(e,t){const i=h(`[name="${e}"]`,this.$form);i.length?this.hasSameValue(this.getInputValue(i),t)||(i.is(":checkbox")?(i.val(t?1:0),i.prop("checked",!!t)):i.val(t),"select2"===i.data("toggle")&&i.trigger("change"),this.triggerChangeEvent(e)):console.error(`Input with name ${e} is not present in form.`)}triggerChangeEvent(e){const t=document.querySelector(`[name="${e}"]`);if(!t)return;const i=document.createEvent("HTMLEvents");i.initEvent("change",!1,!0),t.dispatchEvent(i)}hasSameValue(e,t){if("boolean"==typeof e||"boolean"==typeof t)return e===t;const i=new(p())(d(t));return!!new(p())(d(e)).isEqualTo(i)||t==e}updateObjectByKey(e,t){const i=e.split("."),r=h.serializeJSON.deepGet(this.model,i);if(r===t)return;h.serializeJSON.deepSet(this.model,i,t);const n=new m(this.model,e,t,r);if(Object.prototype.hasOwnProperty.call(this.watchedProperties,e)){this.watchedProperties[e].forEach((e=>{n.isPropagationStopped()||e(n)}))}}initFormMapping(){Object.keys(this.fullModelMapping).forEach((e=>{const t=this.fullModelMapping[e];Array.isArray(t)?t.forEach((t=>{this.addFormMapping(t,e)})):this.addFormMapping(t,e)}))}addFormMapping(e,t){Object.prototype.hasOwnProperty.call(this.formMapping,e)?console.error(`The form element ${e} is already mapped to ${this.formMapping[e]}`):(this.formMapping[e]=t,this.modelMapping[t]=e)}}
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */const g={"impact.priceTaxExcluded":"combination_form[price_impact][price_tax_excluded]","impact.priceTaxIncluded":"combination_form[price_impact][price_tax_included]","impact.unitPriceTaxExcluded":"combination_form[price_impact][unit_price_tax_excluded]","impact.unitPriceTaxIncluded":"combination_form[price_impact][unit_price_tax_included]","price.ecotaxTaxExcluded":"combination_form[price_impact][ecotax_tax_excluded]","price.ecotaxTaxIncluded":"combination_form[price_impact][ecotax_tax_included]","price.wholesalePrice":"combination_form[price_impact][wholesale_price]","price.finalPriceTaxExcluded":"combination_form[price_impact][final_price_tax_excluded]","price.finalPriceTaxIncluded":"combination_form[price_impact][final_price_tax_included]","product.priceTaxExcluded":"combination_form[price_impact][product_price_tax_excluded]","product.taxRate":"combination_form[price_impact][product_tax_rate]","product.ecotaxTaxExcluded":"combination_form[price_impact][product_ecotax_tax_excluded]","suppliers.defaultSupplierId":"combination_form[default_supplier_id]"};var b=i(4902);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class w{constructor(e,t){this.eventEmitter=t,this.mapper=new f(e,g);const i=this.mapper.getInputsFor("impact.priceTaxExcluded");this.precision=i.data("displayPricePrecision"),this.numberFormatter=b.NumberFormatter.build(null==i?void 0:i.data("priceSpecification"));this.mapper.watch(["impact.priceTaxExcluded","impact.priceTaxIncluded","impact.unitPriceTaxExcluded","impact.unitPriceTaxIncluded","price.ecotaxTaxExcluded","price.ecotaxTaxIncluded","price.wholesalePrice"],(e=>this.updateCombinationPrices(e))),this.updateFinalPrices()}getCombination(){return this.mapper.getModel()}watch(e,t){this.mapper.watch(e,t)}set(e,t){this.mapper.set(e,t)}displayPrice(e){return this.numberFormatter.format(e.toNumber())}updateCombinationPrices(e){var t,i,r,n,o,s;if(new(p())(e.value).isNaN())return e.stopPropagation(),void this.mapper.set(e.modelKey,new(p())(0).toFixed(this.precision));const a=this.getTaxRatio();if(!a.isNaN()){switch(e.modelKey){case"impact.priceTaxIncluded":{const e=null!=(t=this.mapper.getBigNumber("impact.priceTaxIncluded"))?t:new(p())(0);this.mapper.set("impact.priceTaxExcluded",e.dividedBy(a).toFixed(this.precision));break}case"impact.priceTaxExcluded":{const e=null!=(i=this.mapper.getBigNumber("impact.priceTaxExcluded"))?i:new(p())(0);this.mapper.set("impact.priceTaxIncluded",e.times(a).toFixed(this.precision));break}case"price.ecotaxTaxIncluded":{const e=this.getEcoTaxRatio(),t=null!=(r=this.mapper.getBigNumber("price.ecotaxTaxIncluded"))?r:new(p())(0);this.mapper.set("price.ecotaxTaxExcluded",t.dividedBy(e).toFixed(this.precision));break}case"price.ecotaxTaxExcluded":{const e=this.getEcoTaxRatio(),t=(null!=(n=this.mapper.getBigNumber("price.ecotaxTaxExcluded"))?n:new(p())(0)).times(e),i=this.getEcotaxTaxIncluded(t);this.updateImpactForEcotax(i),this.mapper.set("price.ecotaxTaxIncluded",t.toFixed(this.precision));break}case"impact.unitPriceTaxIncluded":{const e=null!=(o=this.mapper.getBigNumber("impact.unitPriceTaxIncluded"))?o:new(p())(0);this.mapper.set("impact.unitPriceTaxExcluded",e.dividedBy(a).toFixed(this.precision));break}case"impact.unitPriceTaxExcluded":{const e=null!=(s=this.mapper.getBigNumber("impact.unitPriceTaxExcluded"))?s:new(p())(0);this.mapper.set("impact.unitPriceTaxIncluded",e.times(a).toFixed(this.precision));break}}this.updateFinalPrices()}}updateImpactForEcotax(e){var t,i;const r=this.getTaxRatio(),n=null!=(t=this.mapper.getBigNumber("price.finalPriceTaxIncluded"))?t:new(p())(0),o=(null!=(i=this.mapper.getBigNumber("product.priceTaxExcluded"))?i:new(p())(0)).times(r),s=n.minus(e).minus(o);this.mapper.set("impact.priceTaxExcluded",s.dividedBy(r).toFixed(this.precision))}updateFinalPrices(){var e,t;const i=this.getTaxRatio(),r=this.getEcoTaxRatio();let n=null!=(e=this.mapper.getBigNumber("product.priceTaxExcluded"))?e:new(p())(0),o=null!=(t=this.mapper.getBigNumber("impact.priceTaxExcluded"))?t:new(p())(0),s=this.getEcotaxTaxExcluded();n.isNaN()&&(n=new(p())(0)),o.isNaN()&&(o=new(p())(0)),s.isNaN()&&(s=new(p())(0));const a=s.times(r),c=n.plus(o),l=c.plus(s),u=c.times(i).plus(a);this.mapper.set("price.finalPriceTaxExcluded",l.toFixed(this.precision)),this.mapper.set("price.finalPriceTaxIncluded",u.toFixed(this.precision));const d=this.mapper.getInputsFor("price.finalPriceTaxExcluded"),h=this.mapper.getInputsFor("price.finalPriceTaxIncluded");d&&d.siblings(".final-price-preview").text(this.displayPrice(l)),h&&h.siblings(".final-price-preview").text(this.displayPrice(u))}getEcotaxTaxExcluded(){var e,t;const i=null!=(e=this.mapper.getBigNumber("price.ecotaxTaxExcluded"))?e:new(p())(0);return i.isNegative()||i.isZero()?null!=(t=this.mapper.getBigNumber("product.ecotaxTaxExcluded"))?t:new(p())(0):i}getEcotaxTaxIncluded(e){var t;if(!e.isNegative()&&!e.isZero())return e;const i=null!=(t=this.mapper.getBigNumber("product.ecotaxTaxExcluded"))?t:new(p())(0),r=this.getEcoTaxRatio();return i.times(r)}getTaxRatio(){var e;let t=null!=(e=this.mapper.getBigNumber("product.taxRate"))?e:new(p())(0);return t.isNaN()&&(t=new(p())(0)),t.dividedBy(100).plus(1)}getEcoTaxRatio(){const e=this.mapper.getInputsFor("price.ecotaxTaxExcluded");if(!e)return new(p())(1);let t;try{t=new(p())(e.data("taxRate"))}catch(e){t=new(p())(NaN)}return t.isNaN()&&(t=new(p())(0)),t.dividedBy(100).plus(1)}}var y=i(9567);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */class S{constructor(e,t,i,r){this.defaultSupplierId=t,this.wholesalePrice=i,this.defaultProductSupplierCallback=r,this.map=(e=>{const t=(t,i)=>`${e}_${t}_${i}`;return{productSuppliersCollection:`${e}`,productSuppliersCollectionRow:".product-suppliers-collection-row",productSuppliersTable:`${e} table`,productsSuppliersTableBody:`${e} table tbody`,productsSuppliersRows:`${e} table tbody .product_supplier_row`,productsSupplierRowSelector:".product_supplier_row",productSupplierRow:{supplierIdInput:e=>t(e,"supplier_id"),supplierNameInput:e=>t(e,"supplier_name"),productSupplierIdInput:e=>t(e,"product_supplier_id"),referenceInput:e=>t(e,"reference"),priceInput:e=>t(e,"price_tax_excluded"),currencyIdInput:e=>t(e,"currency_id"),supplierNamePreview:e=>`#product_supplier_row_${e} .supplier_name .preview`}}})(e),this.$productSuppliersCollection=y(this.map.productSuppliersCollection),this.$collectionRow=this.$productSuppliersCollection.parents(this.map.productSuppliersCollectionRow),this.$productsTable=y(this.map.productSuppliersTable),this.$productsTableBody=y(this.map.productsSuppliersTableBody),this.selectedSuppliers=[],this.productSuppliers={},this.prototypeTemplate=this.$productSuppliersCollection.data("prototype"),this.prototypeName=this.$productSuppliersCollection.data("prototypeName"),this.baseDataForSupplier=this.getBaseDataForSupplier(),this.init()}setSelectedSuppliers(e){this.selectedSuppliers=e;const t=[];this.selectedSuppliers.forEach((e=>{t.push(e.supplierId),this.addSupplier(e)}));Object.keys(this.productSuppliers).forEach((e=>{t.includes(e)||this.removeSupplier(e)})),this.renderSuppliers(),this.memorizeCurrentSuppliers(),this.toggleRowVisibility()}setDefaultSupplierId(e){this.defaultSupplierId=e,this.selectedSuppliers.forEach((t=>{t.isDefault=t.supplierId===e})),this.memorizeCurrentSuppliers(),this.updateDefaultProductSupplier()}updateWholesalePrice(e){this.wholesalePrice=e;const t=this.getDefaultSupplier();if(t){const i=this.map.productSupplierRow;y(i.priceInput(t.supplierId)).val(e).trigger("change")}}init(){this.memorizeCurrentSuppliers(),this.selectedSuppliers=this.getSuppliersFromTable(),this.toggleRowVisibility(),this.$productsTable.on("change",":input",(e=>{this.memorizeCurrentSuppliers();const t=y(e.target).parents(this.map.productsSupplierRowSelector).data("supplierIndex");y(this.map.productSupplierRow.supplierIdInput(t)).val()===this.defaultSupplierId&&this.updateDefaultProductSupplier()}))}updateDefaultProductSupplier(){if(!this.defaultProductSupplierCallback)return;const e=this.getDefaultProductSupplier();e&&(this.wholesalePrice=e.price,this.defaultProductSupplierCallback(e))}addSupplier(e){const t=this.getDefaultProductSupplier(),i=(null==t?void 0:t.price)||this.wholesalePrice;if(void 0===this.productSuppliers[e.supplierId]){const t=Object.create(this.baseDataForSupplier);t.supplierId=e.supplierId,t.supplierName=e.supplierName,t.price=i,this.productSuppliers[e.supplierId]=t}else{const t=this.productSuppliers[e.supplierId];t.removed&&(t.removed=!1,t.price=i)}}removeSupplier(e){Object.prototype.hasOwnProperty.call(this.productSuppliers,e)&&(this.productSuppliers[e].removed=!0)}memorizeCurrentSuppliers(){const e=document.querySelectorAll(this.map.productsSuppliersRows);e.length&&e.forEach((e=>{const t=e.dataset.supplierIndex,i=y(this.map.productSupplierRow.supplierIdInput(t)).val();this.productSuppliers[i]={supplierId:i,productSupplierId:y(this.map.productSupplierRow.productSupplierIdInput(t)).val(),supplierName:y(this.map.productSupplierRow.supplierNameInput(t)).val(),reference:y(this.map.productSupplierRow.referenceInput(t)).val(),price:y(this.map.productSupplierRow.priceInput(t)).val(),currencyId:y(this.map.productSupplierRow.currencyIdInput(t)).val(),isDefault:i===this.defaultSupplierId,removed:!1}}))}getSuppliersFromTable(){const e=[],t=document.querySelectorAll(this.map.productsSuppliersRows);return t.length?(t.forEach((t=>{const i=t.dataset.supplierIndex,r=y(this.map.productSupplierRow.supplierIdInput(i)).val();e.push({supplierId:r,supplierName:y(this.map.productSupplierRow.supplierNameInput(i)).val(),isDefault:r===this.defaultSupplierId})})),e):e}renderSuppliers(){this.$productsTableBody.empty(),this.selectedSuppliers.forEach((e=>{const t=this.productSuppliers[e.supplierId];if(t.removed)return;const i=this.prototypeTemplate.replace(new RegExp(this.prototypeName,"g"),t.supplierId);this.$productsTableBody.append(i);const r=this.map.productSupplierRow;y(r.supplierIdInput(t.supplierId)).val(t.supplierId),y(r.supplierNamePreview(t.supplierId)).html(t.supplierName),y(r.supplierNameInput(t.supplierId)).val(t.supplierName),y(r.productSupplierIdInput(t.supplierId)).val(t.productSupplierId),y(r.referenceInput(t.supplierId)).val(t.reference),y(r.priceInput(t.supplierId)).val(t.price),y(r.currencyIdInput(t.supplierId)).val(t.currencyId)}))}toggleRowVisibility(){0!==this.selectedSuppliers.length?this.showCollectionRow():this.hideCollectionRow()}showCollectionRow(){this.$collectionRow.removeClass("d-none")}hideCollectionRow(){this.$collectionRow.addClass("d-none")}getBaseDataForSupplier(){const e=(new DOMParser).parseFromString(this.prototypeTemplate,"text/html");return{removed:!1,productSupplierId:this.extractFromPrototype(this.map.productSupplierRow.productSupplierIdInput,e),reference:this.extractFromPrototype(this.map.productSupplierRow.referenceInput,e),price:this.extractFromPrototype(this.map.productSupplierRow.priceInput,e),currencyId:this.extractFromPrototype(this.map.productSupplierRow.currencyIdInput,e),isDefault:!1}}extractFromPrototype(e,t){var i;const r=t.querySelector(e(this.prototypeName));return null!=(i=null==r?void 0:r.value)?i:null}getDefaultSupplier(){return this.selectedSuppliers.find((e=>e.isDefault))}getDefaultProductSupplier(){return Object.values(this.productSuppliers).find((e=>e.isDefault))}}
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
const{$:x}=window;x((()=>{window.prestashop.component.initComponents(["TranslatableField","TinyMCEEditor","TranslatableInput","EventEmitter","TextWithLengthCounter","DeltaQuantityInput","DisablingSwitch"]);const e=x(s),{eventEmitter:t}=window.prestashop.instance,i=new w(e,t),r=new S(a.productSuppliers,i.getCombination().suppliers.defaultSupplierId,i.getCombination().price.wholesalePrice,(e=>{i.set("price.wholesalePrice",e.price)}));i.watch("price.wholesalePrice",(e=>{r.updateWholesalePrice(e.value)})),new o}))})(),window.combination_form=r})();