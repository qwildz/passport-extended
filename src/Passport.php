<?php

namespace Qwildz\PassportExtended;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport as LaravelPassport;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Passport extends LaravelPassport
{
    /**
     * Indicates if Passport should use client_key instead of client_id.
     *
     * @var bool
     */
    public static $usesHashids = false;

    /**
     * @inheritdoc
     */
    public static function routes($callback = null, array $options = [])
    {
        $defaultOptions = [
            'namespace' => '\Qwildz\PassportExtended\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        parent::routes($callback, $options);

        $defaultOptions = [
            'prefix' => 'session',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $router->group(['middleware' => ['auth:api']], function ($router) {
                $router->post('/set-sid', [
                    'uses' => 'SessionController@setSessionId',
                ]);
            });

            $router->view('/op-frame', 'opframe');

            $router->group(['middleware' => ['api', 'throttle']], function ($router) {
                $router->delete('/{token}', [
                    'uses' => 'SessionController@endSession',
                ]);
            });
        });
    }

    /**
     * Configure Passport to use client_key.
     *
     * @return static
     */
    public static function useHashids()
    {
        static::$usesHashids = true;

        return new static;
    }

    public static function sendSLORequest($endpoint, $secret, $aud, $sid, $jti = null, $sub = null)
    {
        $builder = (new Builder())
            ->setIssuer(config('app.url'))
            ->setAudience($aud)
            ->setIssuedAt(time())
            ->set('sid', $sid)
            ->set('events', ['http://schemas.openid.net/event/backchannel-logout' => (object)[]]);

        if($jti) $builder->setId($jti);
        if($sub) $builder->setSubject($sub);

        $logoutToken = $builder->sign(new Sha256(), $secret)
            ->getToken();

        try {
            $httpClient = new Client();
            $response = $httpClient->post($endpoint, [
                'form_params' => [
                    'token' => (string) $logoutToken,
                ]
            ]);

            if($response->getStatusCode() == 200 || (int) $response->getBody() == 200) {
                return true;
            }
        } catch (Exception $exception) {}

        return false;
    }

    public static function generateClientKey()
    {
        $clients = Client::whereNull('key')->get();
        foreach ($clients as $client) {
            $client->key = str_random(config('passport-extended.client.key_length', 20));
            $client->save();
        }
    }
}
