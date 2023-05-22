<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Controller;

use ICTECHBackendLoginByOTP\OAuth2\Server\CustomAuthorizationServer;
use ICTECHBackendLoginByOTP\Service\EmailService;
use League\OAuth2\Server\AuthorizationServer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use Shopware\Core\Framework\RateLimiter\RateLimiter;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class BackendLoginByOtpController extends AbstractController
{
    /**
     * @var EntityRepository
     */
    private EntityRepository $userRepository;

    /**
     * @var EmailService
     */
    private EmailService $emailService;

    /**
     * @var EntityRepository
     */
    private EntityRepository $backendLoginByOtpRepository;

    private PsrHttpFactory $psrHttpFactory;

    private RateLimiter $rateLimiter;

    private AuthorizationServer $authorizationServer;

    private CustomAuthorizationServer $customAuthorizationServer;

    public function __construct(
        EntityRepository $userRepository,
        EmailService $emailService,
        EntityRepository $backendLoginByOtpRepository,
        PsrHttpFactory $psrHttpFactory,
        RateLimiter $rateLimiter,
        AuthorizationServer $authorizationServer,
        CustomAuthorizationServer $customAuthorizationServer
    ) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
        $this->backendLoginByOtpRepository = $backendLoginByOtpRepository;
        $this->psrHttpFactory = $psrHttpFactory;
        $this->rateLimiter = $rateLimiter;
        $this->authorizationServer = $authorizationServer;
        $this->customAuthorizationServer = $customAuthorizationServer;
    }

    // Send OTP on user email address

    /**
     * @param Context $context
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    /**
     * @Route("/api/backend/login/generateotp", name="api.action.backend.login.generateotp", defaults={"auth_required"=false}, methods={"POST"})
     */
    public function generateLoginOtp(Context $context, Request $request): JsonResponse
    {
        $response = [];

        $userResult = $this->getUserDetails($request, $context);
        if (! $userResult->count()) {
            $response['type'] = 'notfound';
            return new JsonResponse($response);
        }
        $userDetails = $userResult->first();
        $userId = $userDetails->getId();

        // searching UUID in BackendLoginByOtp repository
        $backendOtpDetails = $this->getBackendLoginByOtp($userId, $context);

        //four-digit random otp generation
        $four_digit_random_number = substr(str_shuffle('0123456789'), 0, 4);

        $backendOtpData = [];
        if ($backendOtpDetails !== null) {
            $backendOtpData = [
                'id' => $backendOtpDetails->getId(),
                'userId' => $userId,
                'otp' => strval($four_digit_random_number),
            ];
        } else {
            $backendOtpData = [
                'userId' => $userId,
                'otp' => strval($four_digit_random_number),
            ];
        }
        if (! is_null($backendOtpData)) {
            $otp = $backendOtpData['otp'];
            $result = $this->backendLoginByOtpRepository->upsert([$backendOtpData], $context);
            $this->emailService->sendOTPEMail($userDetails, $context, $otp);
            $response['type'] = 'success';
        } else {
            $response['type'] = 'error';
        }

        return new JsonResponse($response);
    }

    // Get User detail from username
    /**
     * @param array $params
     */
    public function getUserDetails(Request $request, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('username', $request->get('username')));

        return $this->userRepository->search($criteria, $context);
    }

    // Get Backend Login Otp from user id
    public function getBackendLoginByOtp(string $userId, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria, $context);

        return $backendOtpResult->first();
    }

    // Verify OTP
    /**
     * @Route("/api/backend/login/verifyotp", name="api.action.backend.login.verifyotp", defaults={"auth_required"=false}, methods={"POST"})
     */
    public function verifyOtpWithUsername(Request $request): Response
    {
        $responseArray = [];
        $response = new Response();

        try {
            $cacheKey = $request->get('username') . '-' . $request->getClientIp();

            $this->rateLimiter->ensureAccepted(RateLimiter::OAUTH, $cacheKey);
        } catch (RateLimitExceededException $exception) {
            throw new AuthThrottledException($exception->getWaitTime(), $exception);
        }

        $psr7Request = $this->psrHttpFactory->createRequest($request);
        $psr7Response = $this->psrHttpFactory->createResponse($response);

        $response = $this->customAuthorizationServer->respondToAccessTokenRequest($psr7Request, $psr7Response);

        $this->rateLimiter->reset(RateLimiter::OAUTH, $cacheKey);
        return (new HttpFoundationFactory())->createResponse($response);

        return new JsonResponse($responseArray);
    }

    // get backend login otp from user id
    public function getBackendLoginByOtpWithUserName(string $userId, string $otp, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $criteria->addFilter(new EqualsFilter('otp', $otp));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria, $context);

        return $backendOtpResult->first();
    }
}
