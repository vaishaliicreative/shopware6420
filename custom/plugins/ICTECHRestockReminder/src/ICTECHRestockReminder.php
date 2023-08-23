<?php

declare(strict_types=1);

namespace ICTECHRestockReminder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class ICTECHRestockReminder extends Plugin
{
    public const TEMPLATE_TYPE_NAME = 'ProductRestockReminder';
    public const TEMPLATE_TYPE_TECHNICAL_NAME = 'product_restock_reminder';

    public const CONTAIN_PLAIN_EN = 'Hello,\nThere is a reminder for a stock refill of the products. Please find the attachment for the list of the products with low stock. Kindly check and review the products.\n\nKind Regards,\nYours';
    public const CONTAIN_PLAIN_DE = 'Hallo,\nEs gibt eine Erinnerung an die Auffüllung der Lagerbestände der Produkte. In der Anlage finden Sie eine Liste der Produkte mit niedrigem Lagerbestand. Bitte prüfen Sie die Produkte und überprüfen Sie sie.\n\n Mit freundlichen Grüßen\n Deine';
    public const CONTAIN_PLAIN_NL = 'Hallo,\nEr is een herinnering voor een voorraad navulling van de producten. Gelieve de bijlage te vinden voor de lijst van de producten met weinig voorraad. Gelieve de producten te controleren en te beoordelen.\n\nMet vriendelijke groeten,\nDe jouwe';

    public const CONTAIN_HTML_EN = 'Hello,<br>There is a reminder for a stock refill of the products. Please find the attachment for the list of the products with low stock. Kindly check and review the products.<br/><br/>Kind Regards,<br/>Yours';
    public const CONTAIN_HTML_DE = 'Hallo,<br>Es gibt eine Erinnerung an die Auffüllung der Lagerbestände der Produkte. In der Anlage finden Sie eine Liste der Produkte mit niedrigem Lagerbestand. Bitte prüfen Sie die Produkte und überprüfen Sie sie.<br/><br/> Mit freundlichen Grüßen,<br/> Deine';
    public const CONTAIN_HTML_NL = 'Hallo,<br>Er is een herinnering voor een voorraad navulling van de producten. Gelieve de bijlage te vinden voor de lijst van de producten met weinig voorraad. Gelieve de producten te controleren en te beoordelen.<br/><br/>Met vriendelijke groeten,<br/>De jouwe';

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        /**
         * @var EntityRepositoryInterface $mailTemplateTypeRepository
         */
        $mailTemplateTypeRepository = $this->container->get(
            'mail_template_type.repository'
        );

        /**
         * @var EntityRepositoryInterface $mailTemplateRepository
         */
        $mailTemplateRepository = $this->container->get(
            'mail_template.repository'
        );

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
                    'en-GB' => 'Admin',
                    'de-DE' => 'Administratorin',
                    'nl-NL' => 'Deheerder',
                ],
                'subject' => [
                    'en-GB' => 'Product stock reminder',
                    'de-DE' => 'Erinnerung an den Produktbestand',
                    'nl-NL' => 'Herinnering productvoorraad',
                ],
                'contentPlain' => [
                    'en-GB' => self::CONTAIN_PLAIN_EN,
                    'de-DE' => self::CONTAIN_PLAIN_DE,
                    'nl-NL' => self::CONTAIN_PLAIN_NL,
                ],
                'contentHtml' => [
                    'en-GB' => self::CONTAIN_HTML_EN,
                    'de-DE' => self::CONTAIN_HTML_DE,
                    'nl-NL' => self::CONTAIN_HTML_NL,
                ],
            ],
        ];
        try {
            $mailTemplateTypeRepository->create(
                $mailTemplateType,
                $installContext->getContext()
            );
            $mailTemplateRepository->create(
                $mailTemplate,
                $installContext->getContext()
            );
        } catch (UniqueConstraintViolationException $exception) {

        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);

        /** @var EntityRepositoryInterface $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get(
            'mail_template_type.repository'
        );
        /** @var EntityRepositoryInterface $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get(
            'mail_template.repository'
        );

        /** @var MailTemplateTypeEntity $myCustomMailTemplateType */
        $myCustomMailTemplateType = $mailTemplateTypeRepository->search(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter(
                        'technicalName',
                        self::TEMPLATE_TYPE_TECHNICAL_NAME
                    )
                ),
            $uninstallContext->getContext()
        )->first();

        $mailTemplateIds = $mailTemplateRepository->searchIds(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter(
                        'mailTemplateTypeId',
                        $myCustomMailTemplateType->getId()
                    )
                ),
            $uninstallContext->getContext()
        )->getIds();

        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $mailTemplateIds);

        $mailTemplateRepository->delete($ids, $uninstallContext->getContext());

        $mailTemplateTypeRepository->delete([
            ['id' => $myCustomMailTemplateType->getId()],
        ], $uninstallContext->getContext());

        $connection->executeStatement("DELETE FROM `system_config` WHERE `configuration_key` LIKE 'ICTECHRestockReminder%'");
    }


}
