<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Controller;

use ICTECHBackendLoginByOTP\Service\EmailService;
use League\OAuth2\Server\AuthorizationServer;
//use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\OAuthServerException;
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
use function Psl\Str\Byte\length;

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

    public function __construct(
        EntityRepository $userRepository,
        EmailService $emailService,
        EntityRepository $backendLoginByOtpRepository,
        PsrHttpFactory $psrHttpFactory,
        RateLimiter $rateLimiter,
        AuthorizationServer $authorizationServer
    ) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
        $this->backendLoginByOtpRepository = $backendLoginByOtpRepository;
        $this->psrHttpFactory = $psrHttpFactory;
        $this->rateLimiter = $rateLimiter;
        $this->authorizationServer = $authorizationServer;
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
        $four_digit_random_number = substr(str_shuffle("0123456789"), 0, 4);

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
            $this->emailService->sendOTPEMail($userDetails, $context, $otp);
            $this->backendLoginByOtpRepository->upsert([$backendOtpData], $context);
            $response['type'] = 'success';
        } else {
            $response['type'] = 'error';
        }

        return new JsonResponse($response);
    }

    /**
     * @param array $params
     */
    public function getUserDetails(Request $request, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('username', $request->get('username')));

        return $this->userRepository->search($criteria, $context);
    }

    public function getBackendLoginByOtp(string $userId, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria, $context);

        return $backendOtpResult->first();
    }

    /**
     * @Route("/api/backend/login/verifyotp", name="api.action.backend.login.verifyotp", defaults={"auth_required"=false}, methods={"POST"})
     */
    public function verifyOtpWithUsername(Context $context, Request $request): JsonResponse
    {
        $responseArray = [];
        $response = new Response();

        $userDetails = $this->getUserDetails($request, $context)->first();
        $userId = $userDetails->getId();
        $otp = $request->get('otp');

        // searching UUID in BackendLoginByOtp repository
        $backendOtpDetails = $this->getBackendLoginByOtpWithUserName($userId, $otp, $context);
        if ($backendOtpDetails !== null) {
            $request->request->add(['password' => 'shopware']);
            $tokenResponse = $this->createAccessToken($request, $response);

            if ($tokenResponse !== null) {
                $responseArray['type'] = 'success';
                $responseArray['access_token'] = $tokenResponse->access_token;
                $responseArray['refresh_token'] = $tokenResponse->refresh_token;
                $responseArray['expires_in'] = $tokenResponse->expires_in;
            } else {
                $responseArray['type'] = 'error';
            }
        } else {
            $responseArray['type'] = 'notfound';
        }
        return new JsonResponse($responseArray);
    }

    public function getBackendLoginByOtpWithUserName(string $userId, string $otp, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $criteria->addFilter(new EqualsFilter('otp', $otp));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria, $context);

        return $backendOtpResult->first();
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * @throws OAuthServerException
     */
    private function createAccessToken($request, $response)
    {
        try {
            $cacheKey = $request->get('username') . '-' . $request->getClientIp();
            $this->rateLimiter->ensureAccepted(RateLimiter::OAUTH, $cacheKey);
        } catch (RateLimitExceededException $exception) {
            throw new AuthThrottledException($exception->getWaitTime(), $exception);
        }
        $psr7Request = $this->psrHttpFactory->createRequest($request);
        $psr7Response = $this->psrHttpFactory->createResponse($response);

        $response = $this->authorizationServer->respondToAccessTokenRequest($psr7Request, $psr7Response);
        $tokenResponse = (new HttpFoundationFactory())->createResponse($response);
//        echo "<pre>";
//        print_r(json_decode($tokenResponse->getContent()));
//        exit;
        $this->rateLimiter->reset(RateLimiter::OAUTH, $cacheKey);

        return json_decode($tokenResponse->getContent());
    }
}
