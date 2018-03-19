<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use Qwildz\PassportExtended\ClientSession;
use Qwildz\PassportExtended\Passport;
use Qwildz\PassportExtended\Token;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SessionController
{
    private $parser;

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

    public function endSession(Request $request, $token)
    {
        if(!$request->has('token'))
            throw new BadRequestHttpException();

        $token = $this->parseJwt($token);

        $key = new CryptKey(
            'file://'.Passport::keyPath('oauth-public.key'),
            null,
            false
        );

        if(! $token->verify(new Sha256(), $key))
            throw new BadRequestHttpException();

        if(! $instance = $this->getTokenInstance($token->getClaim('jti')))
            throw new ModelNotFoundException('Token is not exists.');

        $instance->revoke();

        // DOING BACK CHANNEL LOGOUT

        return ['status' => 'ok'];
    }

    private function getTokenInstance($jti)
    {
        return Token::find($jti);
    }

    private function parseJwt($jwt)
    {
        return $this->parser->parse($jwt);
    }

}