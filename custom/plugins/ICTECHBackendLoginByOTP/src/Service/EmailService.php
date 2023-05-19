<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;

class EmailService
{
    private AbstractMailService $mailService;

    private EntityRepository $userRepository;

    private EntityRepository $mailTemplateRepository;

    private LoggerInterface $logger;
    private SalesChannelContextServiceInterface $salesChannelContextService;

    public function __construct(
        AbstractMailService $mailService,
        EntityRepository $userRepository,
        EntityRepository $mailTemplateRepository,
        LoggerInterface $logger,
        SalesChannelContextServiceInterface $salesChannelContextService
    ) {
        $this->mailService = $mailService;
        $this->userRepository = $userRepository;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->logger = $logger;
        $this->salesChannelContextService = $salesChannelContextService;
    }

    //send login OTP through email
    public function sendOTPEMail(
        object $userDetails,
        Context $context,
        string $otp
    ): void {
        //getting dynamic data
        $username = $userDetails->getUserName();

        $salesChannelContext = $this->salesChannelContextService->get(
            new SalesChannelContextServiceParameters(
                Defaults::SALES_CHANNEL,
                Uuid::randomHex(),
                $context->getLanguageId(),
                null,
                null,
                $context,
                null,
            )
        );
        $salesChannelId = $salesChannelContext->getSalesChannelId();
        if ($userDetails === null) {
            return;
        }
        //getting mail template
        $mailTemplate = $this->getMailTemplate($context);
        $mailTranslations = $mailTemplate->getTranslations();
        $mailTranslation = $mailTranslations->filter(
            function ($element) use ($context) {
                return $element->getLanguageId() === $context->getLanguageId();
            }
        )->first();

        $data = new DataBag();

        // Replace html content dynamic content
        $htmlUserContent = $mailTranslation->getContentHtml();
        $replaceUserContent = str_replace(
            '{username}',
            $username,
            $htmlUserContent
        );
        $replaceUserContent = str_replace(
            '{otp}',
            $otp,
            $replaceUserContent
        );

        // Replace Plain content dynamic content
        $htmlUserContentPlain = $mailTemplate->getTranslation('contentPlain');
        $replaceUserContentPlain = str_replace(
            '{username}',
            $username,
            $htmlUserContentPlain
        );
        $replaceUserContentPlain = str_replace(
            '{otp}',
            $otp,
            $replaceUserContentPlain
        );

        //Sender Name
        $senderName = $mailTemplate->getTranslation('senderName');
        if ($mailTranslation === null) {
            $data->set('senderName', $senderName);
            $data->set('contentHtml', $replaceUserContent);
            $data->set('contentPlain', $replaceUserContentPlain);
            $data->set('subject', $mailTemplate->getTranslation('subject'));
        } else {
            $data->set('senderName', $senderName);
            $data->set('contentHtml', $replaceUserContent);
            $data->set('contentPlain', $replaceUserContentPlain);
            $data->set('subject', $mailTranslation->getSubject());
        }

        $data->set(
            'recipients',
            [
                $userDetails->getEmail() => $userDetails->getEmail(),
            ]
        );
        $data->set('salesChannelId', $salesChannelId);

        try {
            $this->mailService->send($data->all(), $context);
        } catch (\Exception $e) {
            $this->logger->error(
                "Could not send mail:\n {$e->getMessage()} \n 'Error Code:'
                {$e->getCode()} \n Template data: \n".
                json_encode($data->all()) . "\n"
            );
        }
    }

    // Get Mail Template from mail template repository
    private function getMailTemplate(Context $context): ?MailTemplateEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'mailTemplateType.technicalName',
                'email_otp_login_for_administration'
            )
        );
        $criteria->addAssociation('translations');
        return $this->mailTemplateRepository->search(
            $criteria,
            $context
        )->first();
    }
}
