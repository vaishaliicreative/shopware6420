<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Core\Api;

use ICTECHRestockReminder\Core\ProductStock\ProductStockPdfService;
use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ProductTaskController extends AbstractController
{
    private AbstractMailService $mailService;
    private SystemConfigService $systemConfigService;
    private EntityRepository $productRepository;
    private EntityRepository $mailTemplateRepository;
    private AbstractSalesChannelContextFactory $salesChannelContextFactory;
    private ProductStockPdfService $productStockPdfService;
    private EntityRepository $salesChannelRepository;
    private EntityRepository $languageRepository;
    private EntityRepository $userRepository;
    private EntityRepository $systemConfigRepository;
    private EntityRepository $scheduledTaskRepository;

    public function __construct(
        AbstractMailService $mailService,
        SystemConfigService $systemConfigService,
        EntityRepository $productRepository,
        EntityRepository $mailTemplateRepository,
        AbstractSalesChannelContextFactory $salesChannelContextFactory,
        ProductStockPdfService $productStockPdfService,
        EntityRepository $salesChannelRepository,
        EntityRepository $languageRepository,
        EntityRepository $userRepository,
        EntityRepository $systemConfigRepository,
        EntityRepository $scheduledTaskRepository
    ) {
        $this->mailService = $mailService;
        $this->systemConfigService = $systemConfigService;
        $this->productRepository = $productRepository;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->productStockPdfService = $productStockPdfService;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->languageRepository = $languageRepository;
        $this->userRepository = $userRepository;
        $this->systemConfigRepository = $systemConfigRepository;
        $this->scheduledTaskRepository = $scheduledTaskRepository;
    }

    /**
     * @Route("api/ictech/ictechestockReminder", name="api.ictech.ictechrestockreminder", methods={"GET"})
     */
    public function getProducts(Context $context): JsonResponse
    {
        $this->mailTo($context);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    // Mail Sending
    public function mailTo(Context $context): void
    {
        $userCriteria = new Criteria();
        $adminEmail = $this->userRepository->search(
            $userCriteria,
            $context
        )->first();

        $criteria = new Criteria();
        $language = $this->languageRepository->search(
            $criteria,
            $context
        )->getElements();
        $selectedLanguage = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.emailLanguage'
        );
        $languageId = '';

        foreach ($language as $languageIds) {
            if ($languageIds->getName() === $selectedLanguage) {
                $languageId = $languageIds->getId();
            }
        }

        $activation = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.active'
        );

        if ($activation === null || $activation === false) {
            return;
        }
        $email = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.email'
        );
        if ($email === null || $email === false) {
            return;
        }

        $limit = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.stockLimit'
        );
        $criteriaProduct = new Criteria();
        $criteriaProduct->addFilter(
            new RangeFilter('stock', [RangeFilter::LTE => $limit])
        );
        $products = $this->productRepository->search(
            $criteriaProduct,
            $context
        )->getElements();

        if (count($products) > 0) {
            //getting salesChannel id just for sending mail
            $salesChannelId = $this->salesChannelRepository->search(
                $criteria,
                $context
            )->first()->getId();
            $mailTemplate = $this->getMailTemplate($context);
            $mailTranslations = $mailTemplate->getTranslations();
            $mailTranslation = $mailTranslations->filter(function ($element) use ($languageId) {
                return $element->getLanguageId() === $languageId;
            })->first();
            $data = new RequestDataBag();

            $data->set('senderName', $mailTranslation->getSenderName());
            $data->set('contentHtml', $mailTranslation->getContentHtml());
            $data->set('contentPlain', $mailTranslation->getContentPlain());
            $data->set('subject', $mailTranslation->getSubject());
            $data->set(
                'recipients',
                [
                    $email => $email,
                    $adminEmail->getEmail() => $adminEmail->getEmail(),
                ]
            );
            $data->set('salesChannelId', $salesChannelId);
            $data->set(
                'binAttachments',
                [
                    [
                        'content' => $this->generatePdf($context)->getContent(),
                        'fileName' => 'Products-' . date('d-m-Y') . '.pdf',
                        'mimeType' => 'application/pdf',
                    ],
                ]
            );
            $this->mailService->send($data->all(), $context);
        } else {
            return;
        }
    }

    /**
     * @Route("api/ictech/updateScheduledTask", name="api.ictech.updateScheduledTask", methods={"GET"})
     */
    public function updateScheduledTask(Context $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'ICTECH.product_task'));
        $getScheduledTask = $this->scheduledTaskRepository->search(
            $criteria,
            $context
        )->first();

        $now = new \DateTimeImmutable();

        $lastExecutionTimeString = $getScheduledTask->getLastExecutionTime()
            ->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        $lastExecutionTime = new \DateTimeImmutable($lastExecutionTimeString);

        $intervalTime = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.interval'
        );

        $newNextExecutionTime = $lastExecutionTime->modify(
            sprintf('+%d seconds', $intervalTime)
        );

        $taskDataArray = array();

        if ($newNextExecutionTime < $now) {
            $taskDataArray['lastExecutionTime'] = $now;
            $newNextExecutionTime = $now->modify(
                sprintf('+%d seconds', $intervalTime)
            );
        }

        $taskDataArray['id'] = $getScheduledTask->getId();
        $taskDataArray['status'] = ScheduledTaskDefinition::STATUS_SCHEDULED;
        $taskDataArray['nextExecutionTime'] = $newNextExecutionTime;
        $taskDataArray['runInterval'] = (int) $intervalTime;

        $this->scheduledTaskRepository->update(
            [$taskDataArray],
            $context
        );

        $this->systemConfigService->set(
            'ICTECHRestockReminder.restock.status',
            ScheduledTaskDefinition::STATUS_SCHEDULED
        );

        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }

    private function getMailTemplate(Context $context): ?MailTemplateEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'mailTemplateType.technicalName',
                'product_restock_reminder'
            )
        );
        $criteria->addAssociation('translations');

        return $this->mailTemplateRepository->search(
            $criteria,
            $context
        )->first();
    }

    //Create PDF
    private function generatePdf(Context $context): Response
    {
        $pdfFile = $this->productStockPdfService->createPdfForProductStock(
            $context
        );

        return $this->createResponse(
            $pdfFile->getFileName(),
            $pdfFile->getBlobContent(),
            false,
            'application/pdf'
        );
    }

    private function createResponse(
        string $filename,
        string $content,
        bool $forceDownload,
        string $contentType
    ): Response {
        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            $forceDownload ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $filename,
            preg_replace('/[\x00-\x1F\x7F-\xFF]/', '_', $filename)
        );
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
