<?php

namespace Qwildz\PassportExtended;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'payload',
    ];

    public $timestamps = null;

    /**
     * Get all of the authentication codes for the current sso session.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authCodes()
    {
        return $this->hasMany(Passport::authCodeModel());
    }

    /**
     * Get all of the tokens that belong to the current sso session.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function tokens()
    {
        return $this->hasManyThrough(Passport::tokenModel(), Passport::authCodeModel());
    }
}
