<?php

namespace Qwildz\PassportExtended\Bridge;

use DateTime;
use Illuminate\Contracts\Session\Session;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\TokenRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Events\AccessTokenCreated;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

class AccessTokenRepository extends PassportAccessTokenRepository
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
     * @param  \Laravel\Passport\TokenRepository $tokenRepository
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @param  Session $session
     */
    public function __construct(TokenRepository $tokenRepository, Dispatcher $events, Session $session)
    {
        parent::__construct($tokenRepository, $events);

        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->tokenRepository->create([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => $this->scopesToArray($accessTokenEntity->getScopes()),
            'revoked' => false,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
            'session_id' => $this->session->getId(),
        ]);

        $this->events->dispatch(new AccessTokenCreated(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getUserIdentifier(),
            $accessTokenEntity->getClient()->getIdentifier()
        ));
    }
}
