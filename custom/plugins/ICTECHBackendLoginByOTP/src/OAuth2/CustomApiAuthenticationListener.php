<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\OAuth2;

use ICTECHBackendLoginByOTP\OAuth2\Server\CustomAuthorizationServer;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\Framework\Routing\RouteScopeCheckTrait;
use Shopware\Core\Framework\Routing\RouteScopeRegistry;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomApiAuthenticationListener implements EventSubscriberInterface
{
    use RouteScopeCheckTrait;

    private ResourceServer $resourceServer;

    private AuthorizationServer $authorizationServer;

    private UserRepositoryInterface $userRepository;

    private RefreshTokenRepositoryInterface $refreshTokenRepository;

    private PsrHttpFactory $psrHttpFactory;

    private RouteScopeRegistry $routeScopeRegistry;

    private EntityRepository $backendLoginByOtpRepository;

    private EntityRepository $userEntityRepository;

    private CustomAuthorizationServer $customAuthorizationServer;

    public function __construct(
        ResourceServer $resourceServer,
        AuthorizationServer $authorizationServer,
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        PsrHttpFactory $psrHttpFactory,
        RouteScopeRegistry $routeScopeRegistry,
        EntityRepository $backendLoginByOtpRepository,
        CustomAuthorizationServer $customAuthorizationServer,
        EntityRepository $userEntityRepository
    ) {
        $this->resourceServer = $resourceServer;
        $this->authorizationServer = $authorizationServer;
        $this->userRepository = $userRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->psrHttpFactory = $psrHttpFactory;
        $this->routeScopeRegistry = $routeScopeRegistry;
        $this->backendLoginByOtpRepository = $backendLoginByOtpRepository;
        $this->customAuthorizationServer = $customAuthorizationServer;
        $this->userEntityRepository = $userEntityRepository;
    }

    // Get Subscribe Event
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setupOAuth', 128],
            ],
            KernelEvents::CONTROLLER => [
                ['validateRequest', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_PRIORITY_AUTH_VALIDATE],
            ],
        ];
    }

    // Set Oauth grant type
    public function setupOAuth(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $tenMinuteInterval = new \DateInterval('PT10M');
        $oneWeekInterval = new \DateInterval('P1W');

        $passwordGrant = new PasswordGrant($this->userRepository, $this->refreshTokenRepository);
        $passwordGrant->setRefreshTokenTTL($oneWeekInterval);

        $refreshTokenGrant = new RefreshTokenGrant($this->refreshTokenRepository);
        $refreshTokenGrant->setRefreshTokenTTL($oneWeekInterval);

        $customGrant = new CustomGrant($this->userRepository, $this->refreshTokenRepository, $this->backendLoginByOtpRepository, $this->userEntityRepository);
        $customGrant->setRefreshTokenTTL($oneWeekInterval);

        $this->customAuthorizationServer->enableGrantType($passwordGrant, $tenMinuteInterval);
        $this->customAuthorizationServer->enableGrantType($refreshTokenGrant, $tenMinuteInterval);
        $this->customAuthorizationServer->enableGrantType(new ClientCredentialsGrant(), $tenMinuteInterval);
        $this->customAuthorizationServer->enableGrantType($customGrant, $tenMinuteInterval);
    }

    // validate request
    public function validateRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('auth_required', true)) {
            return;
        }

        if (!$this->isRequestScoped($request, ApiContextRouteScopeDependant::class)) {
            return;
        }

        $psr7Request = $this->psrHttpFactory->createRequest($event->getRequest());
        $psr7Request = $this->resourceServer->validateAuthenticatedRequest($psr7Request);

        $request->attributes->add($psr7Request->getAttributes());
    }

    // get route scope registry
    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }
}
