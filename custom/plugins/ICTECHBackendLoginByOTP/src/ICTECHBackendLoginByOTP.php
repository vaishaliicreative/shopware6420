<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class ICTECHBackendLoginByOTP extends Plugin
{
    public const TEMPLATE_TYPE_NAME = 'Email OTP Login For Administration';
    public const TEMPLATE_TYPE_TECHNICAL_NAME = 'email_otp_login_for_administration';

    public const SUBJECT_ENG = 'Login with Email OTP';
    public const SUBJECT_DE = 'Melden Sie sich mit E-Mail-OTP an';

    public const CONTAIN_PLAIN_EN = "Hello {firstname} {lastname},\n\n Please use the verification code below to login into your account.\n\n Your verification code: {otp} \n\n If you didn't request this, you can ignore this email or let us know.\n\n Yours sincerely Your team.";
    public const CONTAIN_PLAIN_DE = "Hallo {firstname} {lastname},\n\n Bitte verwenden Sie den unten stehenden Bestätigungscode, um sich bei Ihrem Konto anzumelden.\n\n Ihr Bestätigungscode: {otp} \n\n Wenn Sie dies nicht angefordert haben, können Sie diese E-Mail ignorieren oder uns dies mitteilen.\n\n Mit freundlichen Grüßen Ihr Team.";

    public const CONTAIN_HTML_EN = "Hello {firstname} {lastname},<br><br>Please use the verification code below to login into your account.<br><br> Your verification code: <b>{otp}</b> <br><br>If you didn't request this, you can ignore this email or let us know.<br><br>Yours sincerely Your team.";
    public const CONTAIN_HTML_DE = 'Hallo {firstname} {lastname},<br><br>Bitte verwenden Sie den unten stehenden Bestätigungscode, um sich bei Ihrem Konto anzumelden.<br><br> Ihr Bestätigungscode: <b>{otp}</b> <br><br> Wenn Sie dies nicht angefordert haben, können Sie diese E-Mail ignorieren oder uns dies mitteilen.<br><br> Mit freundlichen Grüßen Ihr Team.';

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->createEmailTemplateForBackendLoginOTP($installContext);
    }

    public function createEmailTemplateForBackendLoginOTP(InstallContext $installContext): void
    {
        /** @var
         * EntityRepositoryInterface $mailTemplateTypeRepository
         */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /**
         * @var EntityRepositoryInterface $mailTemplateRepository
         */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $mailTemplateTypeId = Uuid::randomHex();

        $mailTemplateType = [
            [
                'id' => $mailTemplateTypeId,
                'name' => self::TEMPLATE_TYPE_NAME,
                'technicalName' => self::TEMPLATE_TYPE_TECHNICAL_NAME,
                'availableEntities' => [
                    'product' => 'product',
                    'salesChannel' => 'sales_channel',
                ],
            ],
        ];

        $mailTemplate = [
            [
                'id' => Uuid::randomHex(),
                'mailTemplateTypeId' => $mailTemplateTypeId,
                'senderName' => [
                    'en-GB' => 'Storefront',
                    'de-DE' => 'Storefront',
                ],
                'subject' => [
                    'en-GB' => self::SUBJECT_ENG,
                    'de-DE' => self::SUBJECT_DE,
                ],
                'contentPlain' => [
                    'en-GB' => self::CONTAIN_PLAIN_EN,
                    'de-DE' => self::CONTAIN_PLAIN_DE,
                ],
                'contentHtml' => [
                    'en-GB' => self::CONTAIN_HTML_EN,
                    'de-DE' => self::CONTAIN_HTML_DE,
                ],
            ],
        ];

        /**
         * @var MailTemplateTypeEntity $customMailTemplateType
         */
        $customMailTemplateType = $mailTemplateTypeRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', self::TEMPLATE_TYPE_TECHNICAL_NAME)),
            $installContext->getContext()
        )->first();

        if ($customMailTemplateType !== null) {
            return;
        }
        $mailTemplateTypeRepository->create($mailTemplateType, $installContext->getContext());
        $mailTemplateRepository->create($mailTemplate, $installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `backend_login_by_otp`');
        $this->destroyEmailTemplateForBackendLoginOTP($uninstallContext);
    }

    public function destroyEmailTemplateForBackendLoginOTP(UninstallContext $uninstallContext): void
    {
        /** @var
         * EntityRepositoryInterface $mailTemplateTypeRepository
         */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /**
         * @var EntityRepositoryInterface $mailTemplateRepository
         */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        /**
         * @var MailTemplateTypeEntity $customMailTemplateType
         */
        $customMailTemplateType = $mailTemplateTypeRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', self::TEMPLATE_TYPE_TECHNICAL_NAME)),
            $uninstallContext->getContext()
        )->first();

        $mailTemplateIds = $mailTemplateRepository->searchIds(
            (new Criteria())
                ->addFilter(new EqualsFilter('mailTemplateTypeId', $customMailTemplateType->getId())),
            $uninstallContext
                ->getContext()
        )->getIds();

        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $mailTemplateIds);

        $mailTemplateRepository->delete($ids, $uninstallContext->getContext());
        $mailTemplateTypeRepository->delete([['id' => $customMailTemplateType->getId()]], $uninstallContext->getContext());
    }
}
