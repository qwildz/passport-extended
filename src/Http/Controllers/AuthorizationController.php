<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationController extends PassportAuthorizationController
{
    /**
     * @inheritdoc
     */
    public function authorize(ServerRequestInterface $psrRequest,
                              Request $request,
                              ClientRepository $clients,
                              TokenRepository $tokens)
    {
        $url = app(UrlGenerator::class);
        $clients = app(\Qwildz\PassportExtended\ClientRepository::class);

        return $this->withErrorHandling(function () use ($psrRequest, $request, $clients, $tokens, $url) {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            $scopes = $this->parseScopes($authRequest);

            $token = $tokens->findValidToken(
                $user = $request->user(),
                $client = $clients->find($authRequest->getClient()->getIdentifier())
            );

            // Client is not permit sso, re-login
            if(!$client->sso && $request->session()->get('login_'.$client->getKey().'_'.$request->get('state')) != true) {
                return $this->response->redirectGuest($url->route('login'));
            }

            if (($token && $token->scopes === collect($scopes)->pluck('id')->all()) || $client->trusted) {
                return $this->approveRequest($authRequest, $user);
            }

            $request->session()->put('authRequest', $authRequest);

            return $this->response->view('passport::authorize', [
                'client' => $client,
                'user' => $user,
                'scopes' => $scopes,
                'request' => $request,
            ]);
        });
    }
}
