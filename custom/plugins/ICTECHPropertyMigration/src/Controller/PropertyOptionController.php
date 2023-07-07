<?php

declare(strict_types=1);

namespace ICTECHPropertyMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class PropertyOptionController extends AbstractController
{
    private SystemConfigService $systemConfigService;
    private EntityRepository $languageRepository;
    private EntityRepository $propertyGroupOptionRepository;

    private EntityRepository $propertyGroupRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $propertyGroupOptionRepository,
        EntityRepository $propertyGroupRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    /**
     * @Route("/api/_action/migration/migratepropertyoption",name="api.custom.migration.migratepropertyoption", methods={"POST"})
     */
    public function importPropertyOption(Context $context): Response
    {
        $responseArray = [];

        $conn = (new CommonMySQLController($this->systemConfigService))->getMySqlConnection();
        $offSet = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.propertyOptionCount');

        $totalPropertyOption = $this->getPropertyOptionTotalCount($conn);
        $responseArray['totalPropertyOption'] = $totalPropertyOption;

        $propertyOptionSql = 'SELECT * FROM s_plugin_mofa_product_options_options 
         ORDER BY id ASC LIMIT 1 OFFSET '.$offSet;
        $propertyOptionDetails = mysqli_query($conn, $propertyOptionSql);

        if (mysqli_num_rows($propertyOptionDetails) > 0) {
            while ($row = mysqli_fetch_assoc($propertyOptionDetails)) {
                $this->propertyOptionUpsert($row, $context, $conn);
                $currentCount = $offSet + 1;
                $this->systemConfigService
                    ->set(
                        'ICTECHPropertyMigration.config.propertyOptionCount',
                        $currentCount
                    );
            }
        }

        if ($offSet < $totalPropertyOption) {
            $responseArray['type'] = 'Pending';
            $responseArray['importPropertyOptionCount'] = $offSet + 1;
            $responseArray['message'] = 'Property Option remaining';
        } elseif ($offSet > $totalPropertyOption) {
            $responseArray['type'] = 'Success';
            $responseArray['message'] = 'Property Option Already migrated';
        } else {
            $this->systemConfigService
                ->set('ICTECHPropertyMigration.config.propertyGroupCount', 0);
            $responseArray['type'] = 'Success';
            $responseArray['importPropertyOptionCount'] = $offSet + 1;
            $responseArray['message'] = 'Property Option migrated';
        }
        return new JsonResponse($responseArray);
    }

    // get total count of Property Group Options
    public function getPropertyOptionTotalCount(mysqli $conn): int
    {
        $totalPropertyOptions = 0;
        $propertyOptionCountSql = 'SELECT COUNT(*) as total_property_options
                FROM s_plugin_mofa_product_options_options';
        $propertyOptionCountDetails = mysqli_query(
            $conn,
            $propertyOptionCountSql
        );

        if (mysqli_num_rows($propertyOptionCountDetails) > 0) {
            $row = mysqli_fetch_assoc($propertyOptionCountDetails);

            $totalPropertyOptions = (int) $row['total_property_options'];
        }
        return $totalPropertyOptions;
    }

    // add/update property group option data in property group option repository
    public function propertyOptionUpsert(
        array $row,
        Context $context,
        mysqli $conn
    ): void {
        $propertyOptionDetail = $this->checkOptionExistsInPropertyOptionTable(
            $context,
            $row['id']
        );
        if ($propertyOptionDetail === null) {
            $propertyGroupOptionId = Uuid::randomHex();
        } else {
            $propertyGroupOptionId = $propertyOptionDetail->getId();
        }

        $propertyGroupData = $this->getPropertyGroupId(
            $context,
            $row['GroupID']
        );
        $propertyGroupId = $propertyGroupData->getId();

        $propertyOptionDataDetails = $this->getPropertyGroupOptionDetailsFromCore(
            $conn,
            $row['id']
        );

        $languageDetails = (new LanguageController($this->languageRepository))
            ->getLanguagesDetail($context);
        $defaultLanguageCode = (new LanguageController($this->languageRepository))
            ->getDefaultLanguageCode($context);

        $propertyGroupOptionArray = [];

        if (count($propertyOptionDataDetails)) {
            foreach ($propertyOptionDataDetails as $_option) {
                foreach ($languageDetails as $language) {
                    $customFieldsData = [];
                    $languageCode = $language->getTranslationCode()->getCode();
                    $optionLan = str_replace('_', '-', $_option['locale']);
                    if ($optionLan === $languageCode) {
                        if ($_option['Name'] !== '' && $_option['Name'] !== null) {
                            $propertyGroupOptionArray['name'][$languageCode] = $_option['Name'];
                        } else {
                            $propertyGroupOptionArray['name'][$languageCode] = 'dummy migration';
                        }

                        if ($_option['ShortDescription'] !== null) {
                            $propertyGroupOptionArray['description'][$languageCode] = $_option['ShortDescription'];
                        } else {
                            $propertyGroupOptionArray['description'][$languageCode] = $_option['LongDescription'];
                        }
                        $customFieldsData['custom_property_group_option_id'] = $_option['OptionID'];
                        $propertyGroupOptionArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                    }
                }
                if ($propertyOptionDetail === null) {
                    if (! isset($propertyGroupOptionArray['name'][$defaultLanguageCode])) {
                        if ($_option['Name'] !== '' && $_option['Name'] !== null) {
                            $propertyGroupOptionArray['name'][$defaultLanguageCode] = $_option['Name'];
                        } else {
                            $propertyGroupOptionArray['name'][$defaultLanguageCode] = 'dummy migration';
                        }

                        if ($_option['ShortDescription'] !== null) {
                            $propertyGroupOptionArray['description'][$defaultLanguageCode] = $_option['ShortDescription'];
                        } else {
                            $propertyGroupOptionArray['description'][$defaultLanguageCode] = $_option['LongDescription'];
                        }
                        $customFieldsData['custom_property_group_option_id'] = $_option['OptionID'];
                        $propertyGroupOptionArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;
                    }
                }
            }

            $propertyGroupOptionArray['id'] = $propertyGroupOptionId;
            $propertyGroupOptionArray['groupId'] = $propertyGroupId;
        }
        $this->propertyGroupOptionRepository->upsert(
            [$propertyGroupOptionArray],
            $context
        );
    }

    // check option in property group option repository
    public function checkOptionExistsInPropertyOptionTable(
        Context $context,
        string $propertyOptionId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_property_group_option_id',
                $propertyOptionId
            )
        );
        return $this->propertyGroupOptionRepository->search(
            $criteria,
            $context
        )->first();
    }

    // get property option details from SW5 table
    public function getPropertyGroupOptionDetailsFromCore(
        mysqli $conn,
        string $optionId
    ): array {
        $propertyOptionDataDetailsArray = [];
        $propertyOptionDataSql = 'SELECT spmpood.*, scl.locale from
             s_plugin_mofa_product_options_options_desc spmpood
             INNER JOIN s_core_locales scl
             ON scl.id=spmpood.LocaleId
             WHERE spmpood.OptionID = '.$optionId;
        $propertyOptionDetails = mysqli_query($conn, $propertyOptionDataSql);
        if (mysqli_num_rows($propertyOptionDetails) > 0) {
            while ($optionData = mysqli_fetch_assoc($propertyOptionDetails)) {
                $propertyOptionDataDetailsArray[] = $optionData;
            }
        }
        return $propertyOptionDataDetailsArray;
    }

    // get Property Group ID from property group repository
    public function getPropertyGroupId(
        Context $context,
        string $propertyGroupId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_property_group_id',
                $propertyGroupId
            )
        );
        return $this->propertyGroupRepository->search(
            $criteria,
            $context
        )->first();
    }
}
