<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ config('app.name') }} - OP Frame</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/sha256.min.js"></script>
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