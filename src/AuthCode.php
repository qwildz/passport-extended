<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    /**
     * @inheritdoc
     */
    protected $casts = [
        'revoked' => 'bool',
        'session_id' => 'string',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

}
