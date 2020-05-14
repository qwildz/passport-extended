<?php

namespace Qwildz\PassportExtended\Bridge;

use Laravel\Passport\Bridge\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Qwildz\PassportExtended\Passport;
use RuntimeException;
use Vinkla\Hashids\Facades\Hashids;

class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    /**
     * Create a new token instance.
     *
     * @param  string  $userIdentifier
     * @param  array  $scopes
     * @param  \League\OAuth2\Server\Entities\ClientEntityInterface  $client
     * @return void
     */
    public function __construct($userIdentifier, array $scopes, ClientEntityInterface $client)
    {
        $this->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }

        $this->setClient($client);
    }

    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return Token
     */
    private function convertToJWT(CryptKey $privateKey)
    {
        if(Passport::$usesHashids) {
            $aud = Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->encode($this->getClient()->getIdentifier());
        } else {
            $aud = $this->getClient()->getIdentifier();
        }

        $builder = (new Builder())
            ->permittedFor($aud)
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(time())
            ->issuedBy(config('app.url'))
            ->canOnlyBeUsedAfter(time())
            ->expiresAt($this->getExpiryDateTime()->getTimestamp())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        $this->setAdditionalClaims($builder);

        return $builder->getToken(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()));
    }

    private function setAdditionalClaims(Builder $builder)
    {
        $provider = config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'additionalClaims')) {
            $user = (new $model)->find($this->getUserIdentifier());

            if (! $user) {
                return;
            }

            $claims = $user->additionalClaims();

            foreach($claims as $claim) {
                $builder->withClaim($claim['name'], $claim['value']);
            }
        }
    }

    /**
     * Generate a string representation from the access token
     */
    public function __toString()
    {
        return (string) $this->convertToJWT($this->privateKey);
    }
}
