<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\ClientRepository as PassportClientRepository;

class ClientRepository extends PassportClientRepository
{

    /**
     * Store a new client.
     *
     * @param  int $userId
     * @param  string $name
     * @param  string $redirect
     * @param  bool $personalAccess
     * @param  bool $password
     * @param bool $trusted
     * @param bool $sso
     * @return \Laravel\Passport\Client
     */
    public function create($userId, $name, $redirect, $personalAccess = false, $password = false, $trusted = false, $sso = false)
    {
        $client = (new Client)->forceFill([
            'user_id' => $userId,
            'name' => $name,
            'secret' => str_random(40),
            'redirect' => $redirect,
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'revoked' => false,
            'trusted' => $trusted,
            'sso' => $sso,
        ]);

        $client->save();

        return $client;
    }
}
