<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\Passport as LaravelPassport;

class Passport extends LaravelPassport
{
    /**
     * Indicates if Passport should use client_key instead of client_id.
     *
     * @var bool
     */
    public static $usesClientKey = false;

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
    }

    /**
     * Configure Passport to use client_key.
     *
     * @return static
     */
    public static function useClientKey()
    {
        static::$usesClientKey = true;

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
