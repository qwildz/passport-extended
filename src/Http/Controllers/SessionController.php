<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HmacSha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use League\OAuth2\Server\CryptKey;
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
        $instance = $this->getTokenInstance($token->getClaim('jti'));
        $client = $instance->client;

        $encrypter = new Encrypter(hash('md5', $client->secret), 'AES-256-CBC');

        try {
            $sid = $encrypter->decrypt($request->get('sid'));

            $clientSession = new ClientSession();
            $clientSession->id = $sid;
            $clientSession->token_id = $instance->id;
            $clientSession->revoked = false;
            $clientSession->save();

            return ['status' => 'ok'];
        } catch (DecryptException $exception) {
            throw new UnprocessableEntityHttpException('Cannot decrypt the sid.');
        }
    }

    public function endSession(Builder $builder, Client $httpClient, $token)
    {
        $token = $this->parseJwt($token);

        $key = $this->makeCryptKey('oauth-public.key');

        if(! $token->verify(new RsaSha256(), new Key($key->getKeyPath(), $key->getPassPhrase())))
            throw new BadRequestHttpException();

        if(! $instance = $this->getTokenInstance($token->getClaim('jti')))
            throw new ModelNotFoundException('Token is invalid.');

        $instance->revoke();

        if($instance->authCode) {
            $session = Session::with('authCodes.token.clientSession', 'authCodes.client')->find($instance->authCode->session_id);

            foreach($session->authCodes as $code) {
                if(! $code->client->slo || ! $code->token) continue;
                if(! $code->token->clientSession) continue;
                if($code->token->clientSession->revoked) continue;

                $sub = $instance->user_id;
                $sid = $code->token->clientSession->id;
                $slo = $code->client->slo;
                $aud = (Passport::$usesHashids) ? $code->client->key : $code->client->id;
                $jti = $code->token->id;

                $secret = $code->client->secret;

                $logoutToken = $builder->unsign()
                    ->setIssuer(config('app.url'))
                    ->setSubject($sub)
                    ->setAudience($aud)
                    ->setIssuedAt(time())
                    ->setId($jti)
                    ->set('sid', $sid)
                    ->set('events', ['http://schemas.openid.net/event/backchannel-logout' => (object)[]])
                    ->sign(new HmacSha256(), $secret)
                    ->getToken();

                try {
                    $response = $httpClient->post($slo, [
                        'form_params' => [
                            'token' => (string) $logoutToken,
                        ]
                    ]);

                    if($response->getStatusCode() == 200 || (int) $response->getBody() == 200) {
                        $code->token->clientSession->revoke();
                    }
                } catch (Exception $exception) {}
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