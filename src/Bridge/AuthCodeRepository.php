<?php

namespace Qwildz\PassportExtended\Bridge;

use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Connection;
use Laravel\Passport\Bridge\AuthCodeRepository as PassportAuthCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use Qwildz\PassportExtended\Passport;

class AuthCodeRepository extends PassportAuthCodeRepository
{
    /**
     * The database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * The session.
     *
     * @var Session
     */
    protected $session;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Database\Connection $database
     * @param  Session $session
     */
    public function __construct(Connection $database, Session $session)
    {
        parent::__construct($database);

        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $attributes = [
            'id' => $authCodeEntity->getIdentifier(),
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
            'scopes' => $this->formatScopesForStorage($authCodeEntity->getScopes()),
            'revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
            'session_id' => $this->session->getId(),
        ];

        Passport::authCode()->setRawAttributes($attributes)->save();
    }
}
