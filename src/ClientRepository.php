<?php

namespace Qwildz\PassportExtended;

use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Vinkla\Hashids\Facades\Hashids;

class ClientRepository extends PassportClientRepository
{
    /**
     * @inheritdoc
     */
    public function find($id, $useHashids = true)
    {
        if (Passport::$usesHashids && $useHashids) {
            $id = Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->decode($id)[0];
        }

        return parent::find($id);
    }

    /**
     * Get a client instance for the given ID and user ID.
     *
     * @param int $clientId
     * @param mixed $userId
     * @param bool $useHashids
     * @return \Laravel\Passport\Client|Client|null
     */
    public function findForUser($clientId, $userId, $useHashids = true)
    {
        if (Passport::$usesHashids && $useHashids) {
            $clientId = Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->decode($clientId)[0];
        }

        return parent::findForUser($clientId, $userId);
    }


    /**
     * Store a new client.
     *
     * @param int $userId
     * @param string $name
     * @param string $redirect
     * @param bool $personalAccess
     * @param bool $password
     * @param bool $confidential
     * @param bool $trusted
     * @param bool $sso
     * @param string $slo
     * @return \Laravel\Passport\Client
     */
    public function create($userId, $name, $redirect, $personalAccess = false, $password = false, $confidential = true, $trusted = false, $sso = false, $slo = null)
    {
        $client = (Passport::client())->forceFill([
            'user_id' => $userId,
            'name' => $name,
            'secret' => ($confidential || $personalAccess) ? Str::random(config('passport-extended.client.secret_length', 40)) : null,
            'redirect' => $redirect,
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'revoked' => false,
            'trusted' => $trusted,
            'sso' => $sso,
            'slo' => $slo,
        ]);

        $client->save();

        return $client;
    }

    /**
     * Update the given client.
     *
     * @param  Client $client
     * @param  string $name
     * @param  string $redirect
     * @param  bool $trusted
     * @param  bool $sso
     * @param  string $slo
     * @return \Laravel\Passport\Client
     */
    public function update2(Client $client, $name, $redirect, $trusted, $sso, $slo)
    {
        $client->forceFill([
            'name' => $name,
            'redirect' => $redirect,
            'trusted' => $trusted,
            'sso' => $sso,
            'slo' => $slo,
        ])->save();

        return $client;
    }

    /**
     * @inheritdoc
     */
    public function regenerateSecret(\Laravel\Passport\Client $client)
    {
        $client->forceFill([
            'secret' => Str::random(config('passport-extended.client.secret_length', 40)),
        ])->save();

        return $client;
    }

    /**
     * @inheritdoc
     */
    public function revoked($id)
    {
        $client = $this->find($id, false);

        return is_null($client) || $client->revoked;
    }
}
