<?php

namespace Qwildz\PassportExtended\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;

class OpbsCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $generateOpbs = true;

        if($opbs = $request->cookie('opbs')) {
            try {
                if (str_is(hash('sha256', $request->session()->getId()), $opbs)) {
                    $generateOpbs = false;
                }
            } catch (DecryptException $e) {}
        }

        if($generateOpbs) {
            $opbs = hash('sha256', $request->session()->getId());
            $cookie = cookie()->make('opbs', $opbs, (2 * 60 * 60), null, null, true, false, false, null);

            return $response->withCookie($cookie);
        }

        return $response;
    }
}
