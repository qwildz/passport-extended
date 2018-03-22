<?php

namespace Qwildz\PassportExtended;

use Illuminate\Support\Carbon;
use Laravel\Passport\Client as PassportClient;

class ClientSession extends PassportClient
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_sessions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'token_id' => 'string',
        'revoked' => 'bool',
    ];

    protected $dates = [
        'revoked_at'
    ];

    public function token()
    {
        return $this->belongsTo(Token::class);
    }

    public function revoke()
    {
        return $this->forceFill(['revoked' => true, 'revoked_at' => Carbon::now()])->save();
    }

}
