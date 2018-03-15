<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    /**
     * @inheritdoc
     */
    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'bool',
        'session_id' => 'string',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function clientSession()
    {
        return $this->hasOne(ClientSession::class);
    }

}
