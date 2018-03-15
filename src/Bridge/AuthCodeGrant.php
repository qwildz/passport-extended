<?php

namespace Qwildz\PassportExtended\Bridge;

use Illuminate\Database\Connection;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant as LeagueAuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthCodeGrant extends LeagueAuthCodeGrant
{
    /**
     * @var bool
     */
    private $enableCodeExchangeProof = false;

    /**
     * @var Connection
     */
    private $database;

    /**
     * @param AuthCodeRepositoryInterface $authCodeRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param \DateInterval $authCodeTTL
     * @param Connection $database
     */
    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        \DateInterval $authCodeTTL,
        Connection $database
    ) {
        $this->database = $database;

        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    )
    {
        // Validate request
        $client = $this->validateClient($request);
        $encryptedAuthCode = $this->getRequestParameter('code', $request, null);

        if ($encryptedAuthCode === null) {
            throw OAuthServerException::invalidRequest('code');
        }

        // Validate the authorization code
        try {
            $authCodePayload = json_decode($this->decrypt($encryptedAuthCode));
            if (time() > $authCodePayload->expire_time) {
                throw OAuthServerException::invalidRequest('code', 'Authorization code has expired');
            }

            if ($this->authCodeRepository->isAuthCodeRevoked($authCodePayload->auth_code_id) === true) {
                throw OAuthServerException::invalidRequest('code', 'Authorization code has been revoked');
            }

            if ($authCodePayload->client_id !== $client->getIdentifier()) {
                throw OAuthServerException::invalidRequest('code', 'Authorization code was not issued to this client');
            }

            // The redirect URI is required in this request
            $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);
            if (empty($authCodePayload->redirect_uri) === false && $redirectUri === null) {
                throw OAuthServerException::invalidRequest('redirect_uri');
            }

            if ($authCodePayload->redirect_uri !== $redirectUri) {
                throw OAuthServerException::invalidRequest('redirect_uri', 'Invalid redirect URI');
            }

            $scopes = [];
            foreach ($authCodePayload->scopes as $scopeId) {
                $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeId);

                if ($scope instanceof ScopeEntityInterface === false) {
                    // @codeCoverageIgnoreStart
                    throw OAuthServerException::invalidScope($scopeId);
                    // @codeCoverageIgnoreEnd
                }

                $scopes[] = $scope;
            }

            // Finalize the requested scopes
            $scopes = $this->scopeRepository->finalizeScopes(
                $scopes,
                $this->getIdentifier(),
                $client,
                $authCodePayload->user_id
            );
        } catch (\LogicException  $e) {
            throw OAuthServerException::invalidRequest('code', 'Cannot decrypt the authorization code');
        }

        // Validate code challenge
        if ($this->enableCodeExchangeProof === true) {
            $codeVerifier = $this->getRequestParameter('code_verifier', $request, null);
            if ($codeVerifier === null) {
                throw OAuthServerException::invalidRequest('code_verifier');
            }

            switch ($authCodePayload->code_challenge_method) {
                case 'plain':
                    if (hash_equals($codeVerifier, $authCodePayload->code_challenge) === false) {
                        throw OAuthServerException::invalidGrant('Failed to verify `code_verifier`.');
                    }

                    break;
                case 'S256':
                    if (
                        hash_equals(
                            hash('sha256', strtr(rtrim(base64_encode($codeVerifier), '='), '+/', '-_')),
                            $authCodePayload->code_challenge
                        ) === false
                    ) {
                        throw OAuthServerException::invalidGrant('Failed to verify `code_verifier`.');
                    }
                    // @codeCoverageIgnoreStart
                    break;
                default:
                    throw OAuthServerException::serverError(
                        sprintf(
                            'Unsupported code challenge method `%s`',
                            $authCodePayload->code_challenge_method
                        )
                    );
                // @codeCoverageIgnoreEnd
            }
        }

        // Issue and persist access + refresh tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $authCodePayload->user_id, $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        $this->database->table('oauth_access_tokens')->where('id', $accessToken->getIdentifier())
            ->update([
                'auth_code_id' => $authCodePayload->auth_code_id,
            ]);

        // Inject tokens into response type
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        // Revoke used auth code
        $this->authCodeRepository->revokeAuthCode($authCodePayload->auth_code_id);

        return $responseType;
    }

}
