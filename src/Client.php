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

    /**
     * Get all of the authentication codes for the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authCodes()
    {
        return $this->hasMany(AuthCode::class, 'client_id');
    }

    /**
     * Get all of the tokens that belong to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Token::class, 'client_id');
    }
}
