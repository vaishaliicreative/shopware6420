<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class ICTECHBackendLoginByOTP extends Plugin
{
    public const TEMPLATE_TYPE_NAME = 'Email OTP Login For Administration';
    public const TEMPLATE_TYPE_TECHNICAL_NAME = 'email_otp_login_for_administration';

    public const SUBJECT_ENG = 'OTP for [Sales Channel Name] admin Login';
    public const SUBJECT_DE = 'Einmalpasswort-OTP für [Sales Channel Name] Admin-Login';

    public const CONTAIN_PLAIN_EN = 'Dear {username},\n\n Please use the following OTP to log in to the Shopware admin.\n\n OTP: {otp} \n\n Thank you for helping us maintain the security of our eCommerce shop.\n\n Best regards,';
    public const CONTAIN_PLAIN_DE = 'Sehr geehrte/r {username},\n\n Bitte verwenden Sie das folgende Einmalpasswort (OTP), um sich bei Ihrem Ecommerce-Backend-Konto anzumelden.\n\n OTP: {otp} \n\n Vielen Dank, dass Sie uns helfen, die Sicherheit unserer Ecommerce-Plattform zu gewährleisten.\n\n Freundliche Grüße,';

    public const CONTAIN_HTML_EN = 'Dear {username},<br><br>Please use the following OTP to log in to the Shopware admin.<br><br> OTP: <b>{otp}</b> <br><br>Thank you for helping us maintain the security of our eCommerce shop.<br><br>Best regards,';
    public const CONTAIN_HTML_DE = 'Sehr geehrte/r {username},<br><br>Bitte verwenden Sie das folgende Einmalpasswort (OTP), um sich bei Ihrem Ecommerce-Backend-Konto anzumelden.<br><br> OTP: <b>{otp}</b> <br><br> Vielen Dank, dass Sie uns helfen, die Sicherheit unserer Ecommerce-Plattform zu gewährleisten.<br><br> Freundliche Grüße,';

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
                    'en-GB' => 'Shopware Administration',
                    'de-DE' => 'Shopware Administration',
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

    public function deactivate(DeactivateContext $context): void
    {
//        $jsCode = <<<'JS'
//        <script>
//            window.sessionStorage.clear();
//        </script>
//    JS;
//
//        $context->addExtension('storefront', $jsCode);
    }
}
