<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\Client as PassportClient;

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

}
