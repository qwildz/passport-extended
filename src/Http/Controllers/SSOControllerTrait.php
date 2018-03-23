<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Qwildz\PassportExtended\Passport;
use Qwildz\PassportExtended\Session;

trait SSOControllerTrait
{
    use SSOTrait, AuthenticatesUsers {
        logout as private passportLogout;
        attemptLogin as private passportAttemptLogin;
    }

    public function showLoginForm(Request $request)
    {
        if(!$this->isSSONeedLogin($request) && $request->user()) {
            return $this->sendLoginResponse($request);
        }

        $client = $this->getClient($request);

        return view('auth.login', compact( 'client'));
    }

    protected function authenticated(Request $request, $user) {
        $this->setSSOHasLogin($request);
    }

    protected function attemptLogin(Request $request)
    {
        if($this->isSSONeedLogin($request) && $request->user()) {
            if($request->user()->{$this->username()} == $request->get($this->username())) {
                return $this->checkLogin($request, false);
            } else {
                return $this->checkLogin($request, true);
            }
        } else {
            return $this->checkLogin($request, true);
        }
    }

    private function checkLogin($request, $regenerate) {
        if($regenerate) {
            $passed = $this->passportAttemptLogin($request);
            if($passed) session()->put('regenerate', true);
            return $passed;
        } else {
            $passed = $this->guard()->once($this->credentials($request));
            if($passed) session()->put('regenerate', false);
            return $passed;
        }
    }

    protected function sendLoginResponse(Request $request)
    {
        if(session()->pull('regenerate', false)) {
            $this->logoutClientSession($request);

            $request->session()->regenerate();
        }

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        $this->logoutClientSession($request);

        return $this->passportLogout($request);
    }

    private function logoutClientSession(Request $request)
    {
        $session = Session::with('authCodes.token.clientSession', 'authCodes.client')->find($request->session()->getId());

        if ($session) {
            foreach ($session->authCodes as $code) {
                // Skip if client doesn't support SLO or auth code hasn't been exchanged into access token
                if (!$code->client->slo || !$code->token) continue;

                // Skip if token doesn't have client session
                if (!$code->token->clientSession) continue;

                // Skip if client session has been revoked
                if ($code->token->clientSession->revoked) continue;

                $sub = $code->user_id;
                $sid = $code->token->clientSession->id;
                $slo = $code->client->slo;
                $aud = (Passport::$usesHashids) ? $code->client->key : $code->client->id;
                $jti = $code->token->id;

                $secret = $code->client->secret;

                if (Passport::sendSLORequest($slo, $secret, $aud, $sid, $jti, $sub)) {
                    $code->token->clientSession->revoke();
                }
            }
        }
    }
}