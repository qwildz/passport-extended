<?php

namespace Qwildz\PassportExtended;

use Laravel\Passport\ClientRepository as PassportClientRepository;

class ClientRepository extends PassportClientRepository
{
    /**
     * @inheritdoc
     */
    public function find($id)
    {
        return Client::find($id);
    }

    /**
     * @inheritdoc
     */
    public function findForUser($clientId, $userId)
    {
        return Client::where('id', $clientId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @inheritdoc
     */
    public function forUser($userId)
    {
        return Client::where('user_id', $userId)
            ->orderBy('name', 'asc')->get();
    }

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
            'secret' => str_random(config('passport-extended.client.secret_length', 40)),
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

    /**
     * @inheritdoc
     */
    public function update(Client $client, $name, $redirect, $trusted, $sso)
    {
        $client->forceFill([
            'name' => $name, 'redirect' => $redirect, 'trusted' => $trusted, 'sso' =>$sso,
        ])->save();

        return $client;
    }

    /**
     * @inheritdoc
     */
    public function regenerateSecret(Client $client)
    {
        $client->forceFill([
            'secret' => str_random(config('passport-extended.client.secret_length', 40)),
        ])->save();

        return $client;
    }
}
