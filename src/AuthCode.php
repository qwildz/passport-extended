<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    /**
     * @inheritdoc
     */
    protected $casts = [
        'id' => 'string',
        'revoked' => 'bool',
        'session_id' => 'string',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function token()
    {
        return $this->hasOne(Passport::tokenModel());
    }
}
