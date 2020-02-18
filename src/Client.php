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

    protected $appends = ['key'];

    public function getKeyAttribute()
    {
        if(Passport::$usesHashids) {
            return Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->encode($this->attributes['id']);
        } else {
            return '-';
        }
    }

    /** inherit */
    public function skipsAuthorization()
    {
        return $this->trusted;
    }
}
