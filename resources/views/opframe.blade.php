<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ config('app.name') }} - OP Frame</title>
    <script>
        !function(t,n){"object"==typeof exports?module.exports=exports=n():"function"==typeof define&&define.amd?define([],n):t.CryptoJS=n()}(this,function(){var t=t||function(t,n){var i=Object.create||function(){function t(){}return function(n){var i;return t.prototype=n,i=new t,t.prototype=null,i}}(),e={},r=e.lib={},o=r.Base=function(){return{extend:function(t){var n=i(this);return t&&n.mixIn(t),n.hasOwnProperty("init")&&this.init!==n.init||(n.init=function(){n.$super.init.apply(this,arguments)}),n.init.prototype=n,n.$super=this,n},create:function(){var t=this.extend();return t.init.apply(t,arguments),t},init:function(){},mixIn:function(t){for(var n in t)t.hasOwnProperty(n)&&(this[n]=t[n]);t.hasOwnProperty("toString")&&(this.toString=t.toString)},clone:function(){return this.init.prototype.extend(this)}}}(),s=r.WordArray=o.extend({init:function(t,i){t=this.words=t||[],i!=n?this.sigBytes=i:this.sigBytes=4*t.length},toString:function(t){return(t||c).stringify(this)},concat:function(t){var n=this.words,i=t.words,e=this.sigBytes,r=t.sigBytes;if(this.clamp(),e%4)for(var o=0;o<r;o++){var s=i[o>>>2]>>>24-o%4*8&255;n[e+o>>>2]|=s<<24-(e+o)%4*8}else for(var o=0;o<r;o+=4)n[e+o>>>2]=i[o>>>2];return this.sigBytes+=r,this},clamp:function(){var n=this.words,i=this.sigBytes;n[i>>>2]&=4294967295<<32-i%4*8,n.length=t.ceil(i/4)},clone:function(){var t=o.clone.call(this);return t.words=this.words.slice(0),t},random:function(n){for(var i,e=[],r=function(n){var n=n,i=987654321,e=4294967295;return function(){i=36969*(65535&i)+(i>>16)&e,n=18e3*(65535&n)+(n>>16)&e;var r=(i<<16)+n&e;return r/=4294967296,r+=.5,r*(t.random()>.5?1:-1)}},o=0;o<n;o+=4){var a=r(4294967296*(i||t.random()));i=987654071*a(),e.push(4294967296*a()|0)}return new s.init(e,n)}}),a=e.enc={},c=a.Hex={stringify:function(t){for(var n=t.words,i=t.sigBytes,e=[],r=0;r<i;r++){var o=n[r>>>2]>>>24-r%4*8&255;e.push((o>>>4).toString(16)),e.push((15&o).toString(16))}return e.join("")},parse:function(t){for(var n=t.length,i=[],e=0;e<n;e+=2)i[e>>>3]|=parseInt(t.substr(e,2),16)<<24-e%8*4;return new s.init(i,n/2)}},u=a.Latin1={stringify:function(t){for(var n=t.words,i=t.sigBytes,e=[],r=0;r<i;r++){var o=n[r>>>2]>>>24-r%4*8&255;e.push(String.fromCharCode(o))}return e.join("")},parse:function(t){for(var n=t.length,i=[],e=0;e<n;e++)i[e>>>2]|=(255&t.charCodeAt(e))<<24-e%4*8;return new s.init(i,n)}},f=a.Utf8={stringify:function(t){try{return decodeURIComponent(escape(u.stringify(t)))}catch(t){throw new Error("Malformed UTF-8 data")}},parse:function(t){return u.parse(unescape(encodeURIComponent(t)))}},h=r.BufferedBlockAlgorithm=o.extend({reset:function(){this._data=new s.init,this._nDataBytes=0},_append:function(t){"string"==typeof t&&(t=f.parse(t)),this._data.concat(t),this._nDataBytes+=t.sigBytes},_process:function(n){var i=this._data,e=i.words,r=i.sigBytes,o=this.blockSize,a=4*o,c=r/a;c=n?t.ceil(c):t.max((0|c)-this._minBufferSize,0);var u=c*o,f=t.min(4*u,r);if(u){for(var h=0;h<u;h+=o)this._doProcessBlock(e,h);var p=e.splice(0,u);i.sigBytes-=f}return new s.init(p,f)},clone:function(){var t=o.clone.call(this);return t._data=this._data.clone(),t},_minBufferSize:0}),p=(r.Hasher=h.extend({cfg:o.extend(),init:function(t){this.cfg=this.cfg.extend(t),this.reset()},reset:function(){h.reset.call(this),this._doReset()},update:function(t){return this._append(t),this._process(),this},finalize:function(t){t&&this._append(t);var n=this._doFinalize();return n},blockSize:16,_createHelper:function(t){return function(n,i){return new t.init(i).finalize(n)}},_createHmacHelper:function(t){return function(n,i){return new p.HMAC.init(t,i).finalize(n)}}}),e.algo={});return e}(Math);return t});
        //# sourceMappingURL=core.min.js.map
        !function(e,r){"object"==typeof exports?module.exports=exports=r(require("./core")):"function"==typeof define&&define.amd?define(["./core"],r):r(e.CryptoJS)}(this,function(e){return function(r){var t=e,o=t.lib,n=o.WordArray,i=o.Hasher,s=t.algo,a=[],c=[];!function(){function e(e){for(var t=r.sqrt(e),o=2;o<=t;o++)if(!(e%o))return!1;return!0}function t(e){return 4294967296*(e-(0|e))|0}for(var o=2,n=0;n<64;)e(o)&&(n<8&&(a[n]=t(r.pow(o,.5))),c[n]=t(r.pow(o,1/3)),n++),o++}();var f=[],h=s.SHA256=i.extend({_doReset:function(){this._hash=new n.init(a.slice(0))},_doProcessBlock:function(e,r){for(var t=this._hash.words,o=t[0],n=t[1],i=t[2],s=t[3],a=t[4],h=t[5],u=t[6],l=t[7],d=0;d<64;d++){if(d<16)f[d]=0|e[r+d];else{var _=f[d-15],p=(_<<25|_>>>7)^(_<<14|_>>>18)^_>>>3,v=f[d-2],H=(v<<15|v>>>17)^(v<<13|v>>>19)^v>>>10;f[d]=p+f[d-7]+H+f[d-16]}var y=a&h^~a&u,w=o&n^o&i^n&i,A=(o<<30|o>>>2)^(o<<19|o>>>13)^(o<<10|o>>>22),S=(a<<26|a>>>6)^(a<<21|a>>>11)^(a<<7|a>>>25),g=l+S+y+c[d]+f[d],m=A+w;l=u,u=h,h=a,a=s+g|0,s=i,i=n,n=o,o=g+m|0}t[0]=t[0]+o|0,t[1]=t[1]+n|0,t[2]=t[2]+i|0,t[3]=t[3]+s|0,t[4]=t[4]+a|0,t[5]=t[5]+h|0,t[6]=t[6]+u|0,t[7]=t[7]+l|0},_doFinalize:function(){var e=this._data,t=e.words,o=8*this._nDataBytes,n=8*e.sigBytes;return t[n>>>5]|=128<<24-n%32,t[(n+64>>>9<<4)+14]=r.floor(o/4294967296),t[(n+64>>>9<<4)+15]=o,e.sigBytes=4*t.length,this._process(),this._hash},clone:function(){var e=i.clone.call(this);return e._hash=this._hash.clone(),e}});t.SHA256=i._createHelper(h),t.HmacSHA256=i._createHmacHelper(h)}(Math),e.SHA256});
        //# sourceMappingURL=sha256.min.js.map
    </script>
    <script>
        if ( self == top ) {
            window.location.href = 'about:blank';
        }

        window.unescape = window.unescape || window.decodeURI;
        window.addEventListener("message",receiveMessage, false);

        function getCookie(c_name)
        {
            var i,x,y,ARRcookies=document.cookie.split(";");
            for (i=0;i<ARRcookies.length;i++)
            {
                x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
                y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
                x=x.replace(/^\s+|\s+$/g,"");
                if (x==c_name)
                {
                    return unescape(y);
                }
            }
        }

        @if(config('app.debug'))
        function printCookies()
        {
            console.log('cookies : <-----');
            var i,x,y,ARRcookies=document.cookie.split(";");
            console.log('cookies = ' + document.cookie);
            for (i=0;i<ARRcookies.length;i++)
            {
                console.log("cookie[" + i + "] = " + ARRcookies[i]);
                x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
                y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
                x=x.replace(/^\s+|\s+$/g,"");
                console.log('C["' + x + '"] = ' + y);
            }
            console.log('cookies : ----->');
        }
        @endif

        function receiveMessage(e){
            var state = '';
            @if(config('app.debug'))
            console.log('opFrame data = ' + e.data);
            @endif
            var parts = e.data.split(' ');
            var client_id = parts[0];
            var session_state = parts[1];
            var ss_parts = session_state.split('.');
            var salt = ss_parts[1];

            var ops = getCookie('opbs');
            @if(config('app.debug'))
            console.log('client_id : ' + client_id + ' origin : ' + e.origin + ' opss : ' + ops + ' salt : ' + salt);
            console.log('opmes crypto input = ' + client_id + e.origin + ops + salt + "." + salt);
            @endif
            var ss = CryptoJS.SHA256(client_id + e.origin + ops + salt) + "." + salt;
            @if(config('app.debug'))
            console.log('calculated ss  = ' + ss);
            @endif
            if (session_state == ss) {
                state = 'unchanged';
            } else {
                @if(config('app.debug'))
                console.log('received:' + session_state + ' != ' + ss);
                @endif
                state = 'changed';
            }
            @if(config('app.debug'))
            console.log('opfram posting : ' + state);
            @endif
            e.source.postMessage(state, e.origin);
        }
    </script>
</head>
<body>
</body>
</html>