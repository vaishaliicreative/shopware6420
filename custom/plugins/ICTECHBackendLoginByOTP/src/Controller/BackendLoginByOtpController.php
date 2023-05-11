<?php declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Controller;

use ICTECHBackendLoginByOTP\Service\EmailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
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
    public function __construct(
        EntityRepository $userRepository,
        EmailService $emailService,
        EntityRepository $backendLoginByOtpRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
        $this->backendLoginByOtpRepository = $backendLoginByOtpRepository;
    }

    // Send OTP on user email address

    /**
     * @param Context $context
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @Route("/api/backend/login/generateotp", name="api.action.backend.login.generateotp", defaults={"auth_required"=false}, methods={"POST"})
     */
    public function generateLoginOtp(Context $context,Request $request):JsonResponse
    {
        $response = [];
        $params = $request->request->get('params');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('username',$params['username']));

        $userResult = $this->userRepository->search($criteria,$context);

        if(!$userResult->count()){
            $response['type'] = "notfound";
            return new JsonResponse($response);
        }
        $userDetails = $userResult->first();
        $userId = $userDetails->getId();

        // searching UUID in BackendLoginByOtp repository

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId',$userId));
        $backendOtpResult = $this->backendLoginByOtpRepository->search($criteria,$context);
        $backendOtpDetails = $backendOtpResult->first();

        //four-digit random otp generation
        $four_digit_random_number = rand(0000,9999);

        $backendOtpData[]= null;
        if(!empty($backendOtpDetails)){
            $backendOtpData = [
                'id' => $backendOtpDetails->getId(),
                'userId' => $userId,
                'otp' => strval($four_digit_random_number)
            ];
        }else{
            $backendOtpData = [
                'userId' => $userId,
                'otp' => strval($four_digit_random_number)
            ];
        }

        if (!is_null($backendOtpData)) {
            $otp = $backendOtpData['otp'];
            $this->emailService->sendOTPEMail($userDetails, $context, $otp);

            $this->backendLoginByOtpRepository->upsert([$backendOtpData], $context);
            $response['type'] = "success";
        }else{
            $response['type'] = "error";
        }

        return new JsonResponse($response);
    }
}
