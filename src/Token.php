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

    /**
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function authCode()
    {
        return $this->belongsTo(AuthCode::class);
    }

    public function clientSession()
    {
        return $this->hasOne(ClientSession::class);
    }
}
