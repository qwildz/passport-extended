<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\Client as PassportClient;
use Vinkla\Hashids\Facades\Hashids;

class Client extends PassportClient
{
    /**
     * @inheritdoc
     */
    protected $casts = [
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
        'trusted' => 'bool',
        'sso' => 'bool',
    ];

    public function getIdAttribute($value)
    {
        if(Passport::$usesHashids) {
            return Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->encode($value);
        } else {
            return $value;
        }
    }

}
