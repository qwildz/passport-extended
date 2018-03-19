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

    /**
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function token()
    {
        return $this->hasOne(Token::class);
    }
}
