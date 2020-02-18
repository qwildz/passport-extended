<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Psr\Http\Message\ServerRequestInterface;
use Qwildz\PassportExtended\Passport;

class AuthorizationController extends PassportAuthorizationController
{
    /**
     * @inheritdoc
     * @throws \Laravel\Passport\Exceptions\OAuthServerException
     */
    public function authorize(ServerRequestInterface $psrRequest,
                              Request $request,
                              ClientRepository $clients,
                              TokenRepository $tokens)
    {
        $url = app(UrlGenerator::class);
        $clients = app(\Qwildz\PassportExtended\ClientRepository::class);

        $authRequest = $this->withErrorHandling(function () use ($psrRequest) {
            return $this->server->validateAuthorizationRequest($psrRequest);
        });

        $scopes = $this->parseScopes($authRequest);

        $token = $tokens->findValidToken(
            $user = $request->user(),
            $client = $clients->find($authRequest->getClient()->getIdentifier(), false)
        );

        // Check if user has access to the client
        if (method_exists($user, 'checkAccessToClient')) {
            if (! $user->checkAccessToClient($client)) {
                return $this->response->redirectGuest('/noaccess?client_id='.$client->key);
            }
        }

        if(Passport::$usesHashids) {
            $key = 'login_'.$client->key.'_'.$request->get('state');
        } else {
            $key = 'login_'.$client->getKey().'_'.$request->get('state');
        }

        // Client is not permit sso, re-login
        if(!$client->sso && $request->session()->get($key) != true) {
            return $this->response->redirectGuest($url->route('login'));
        }

        if (($token && $token->scopes === collect($scopes)->pluck('id')->all()) || $client->skipsAuthorization()) {
            return $this->approveRequest($authRequest, $user);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', $authRequest);

        return $this->response->view('passport::authorize', [
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'request' => $request,
            'authToken' => $authToken,
        ]);
    }
}
