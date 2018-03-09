<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\Passport as LaravelPassport;

class Passport extends LaravelPassport
{
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
}
