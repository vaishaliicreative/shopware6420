<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\OAuth2;

use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestAccessTokenEvent;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\RequestRefreshTokenEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CustomGrant extends AbstractGrant
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        EntityRepository $backendLoginByOtpRepository,
        EntityRepository $userEntityRepository
    ) {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);

        $this->refreshTokenTTL = new DateInterval('P1M');
        $this->backendLoginByOtpRepository = $backendLoginByOtpRepository;
        $this->userEntityRepository = $userEntityRepository;
    }

    public function getIdentifier(): string
    {
        return 'login_otp';
    }

    /**
     * Get access token
     * @throws OAuthServerException
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ):  ResponseTypeInterface {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getUserId());

        // Issue and persist new access token
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getUserId(), $finalizedScopes);
        $this->getEmitter()->emit(new RequestAccessTokenEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request, $accessToken));
        $responseType->setAccessToken($accessToken);

        // Issue and persist new refresh token if given
        $refreshToken = $this->issueRefreshToken($accessToken);

        if ($refreshToken !== null) {
            $this->getEmitter()->emit(new RequestRefreshTokenEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request, $refreshToken));
            $responseType->setRefreshToken($refreshToken);
        }

        return $responseType;
    }

    /**
     * validate user
     * @return Entity
     *
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request)
    {
        $context = Context::createDefaultContext();
        $username = $this->getRequestParameter('username', $request);

        if (!\is_string($username)) {
            throw OAuthServerException::invalidRequest('username');
        }

        $userDetails = $this->getUserDetails($username, $context)->first();
        $userId = $userDetails->getId();

        $otp = $this->getRequestParameter('otp', $request);

        if (!\is_string($otp)) {
            throw OAuthServerException::invalidRequest('otp');
        }

        $user = $this->getBackendLoginByOtpWithUserName($userId, $otp, $context);

        if ($user instanceof Entity === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidCredentials();
        }

        return $user;
    }

    // get backend otp details
    public function getBackendLoginByOtpWithUserName(string $userId, string $otp, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $criteria->addFilter(new EqualsFilter('otp', $otp));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria, $context);

        return $backendOtpResult->first();
    }

    // get user details from username
    public function getUserDetails(string $username, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('username', $username));
        return $this->userEntityRepository->search($criteria, $context);
    }

    /**
     * validate client
     * @throws OAuthServerException
     */
    protected function validateClient(ServerRequestInterface $request): ClientEntityInterface
    {
        [$clientId, $clientSecret] = $this->getClientCredentials($request);

        $client = $this->getClientEntityOrFail($clientId, $request);

        // If a redirect URI is provided ensure it matches what is pre-registered
        $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);

        if ($redirectUri !== null) {
            if (!\is_string($redirectUri)) {
                throw OAuthServerException::invalidRequest('redirect_uri');
            }

            $this->validateRedirectUri($redirectUri, $client, $request);
        }

        return $client;
    }

    /**
     * get client credential
     * @throws OAuthServerException
     */
    protected function getClientCredentials(ServerRequestInterface $request): array
    {
        [$basicAuthUser, $basicAuthPassword] = $this->getBasicAuthCredentials($request);

        $clientId = $this->getRequestParameter('client_id', $request, $basicAuthUser);

        if (\is_null($clientId)) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        $clientSecret = $this->getRequestParameter('client_secret', $request, $basicAuthPassword);

        if ($clientSecret !== null && !\is_string($clientSecret)) {
            throw OAuthServerException::invalidRequest('client_secret');
        }

        return [$clientId, $clientSecret];
    }
}
