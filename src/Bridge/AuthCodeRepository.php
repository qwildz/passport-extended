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
     * The session.
     *
     * @var Session
     */
    protected $session;

    /**
     * Create a new repository instance.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
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
