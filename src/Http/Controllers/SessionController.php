<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use League\OAuth2\Server\CryptKey;
use Qwildz\PassportExtended\ClientRepository;
use Qwildz\PassportExtended\ClientSession;
use Qwildz\PassportExtended\Passport;
use Qwildz\PassportExtended\Session;
use Qwildz\PassportExtended\Token;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SessionController
{
    protected $parser;

    function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function setSessionId(Request $request)
    {
        if(!$request->has('sid')) throw new BadRequestHttpException();

        $token = $this->parseJwt($request->bearerToken());
        $instance = $this->getTokenInstance($token->getClaim('jti'))->load('authCode.session');
        $client = $instance->client;

        //$encrypter = new Encrypter(hash('md5', $client->secret), 'AES-256-CBC');

        try {
            //$sid = $encrypter->decrypt($request->get('sid'));
            $sid = $request->get('sid');

            $clientSession = new ClientSession();
            $clientSession->session_id = $sid;
            $clientSession->token_id = $instance->id;
            $clientSession->revoked = false;
            $clientSession->save();

            $schemeRedirect = parse_url($client->redirect, PHP_URL_SCHEME);
            $hostRedirect = parse_url($client->redirect, PHP_URL_HOST);
            $salt = uniqid(mt_rand());

            $state = hash('sha256', $client->key . $schemeRedirect . '://' . $hostRedirect . hash('sha256', $instance->authCode->session->id) . $salt) . '.' . $salt;

            return ['status' => 'ok', 'state' => $state];
        } catch (DecryptException $exception) {
            throw new UnprocessableEntityHttpException('Cannot decrypt the sid.');
        }
    }

    public function endSession(Request $request, $sid)
    {
        $token = $this->parseJwt($request->bearerToken());

        $key = $this->makeCryptKey('oauth-public.key');

        if(! $token->verify(new RsaSha256(), new Key($key->getKeyPath(), $key->getPassPhrase())))
            throw new BadRequestHttpException();

        if(! $instance = $this->getTokenInstance($token->getClaim('jti')))
            throw new ModelNotFoundException('Token is invalid.');

        $instance->revoke();
        optional($instance->clientSession)->revoke();

        if($instance->authCode) {
            $session = Session::with('authCodes.token.clientSession', 'authCodes.client')->find($instance->authCode->session_id);

            if ($session) {
                foreach ($session->authCodes as $code) {
                    // Skip if client doesn't support SLO or auth code hasn't been exchanged into access token
                    if (!$code->client->slo || !$code->token) continue;

                    // Skip if token doesn't have client session
                    if (!$code->token->clientSession) continue;

                    // Skip if client session has been revoked
                    if ($code->token->clientSession->revoked) continue;

                    // Don't send SLO request to the token's origin
                    if ($instance->id == $code->token->id) continue;

                    $sub = $instance->user_id;
                    $sid = $code->token->clientSession->session_id;
                    $slo = $code->client->slo;
                    $aud = (Passport::$usesHashids) ? $code->client->key : $code->client->id;
                    $jti = $code->token->id;

                    $secret = $code->client->secret;

                    if (Passport::sendSLORequest($slo, $secret, $aud, $sid, $jti, $sub)) {
                        $code->token->revoke();
                        $code->token->clientSession->revoke();
                    }
                }

                session()->setId($instance->authCode->session_id);
                session()->invalidate();
                session()->regenerate(true);
            }
        }

        return ['status' => 'ok'];
    }

    protected function getTokenInstance($jti)
    {
        return Token::with('authCode')->find($jti);
    }

    protected function parseJwt($jwt)
    {
        return $this->parser->parse($jwt);
    }

    protected function makeCryptKey($key)
    {
        return new CryptKey(
            'file://'.Passport::keyPath($key),
            null,
            false
        );
    }

}