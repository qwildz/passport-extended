<?php

namespace Qwildz\PassportExtended;

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport as LaravelPassport;

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

            $router->group(['middleware' => ['api']], function ($router) {
                $router->get('/{token}', [
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

    public static function generateClientKey()
    {
        $clients = Client::whereNull('key')->get();
        foreach ($clients as $client) {
            $client->key = str_random(config('passport-extended.client.key_length', 20));
            $client->save();
        }
    }
}
